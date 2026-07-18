<?php

namespace App\Http\Controllers\jambo;

use App\Http\Controllers\Controller;
use App\Models\jambo\JamboItem;
use App\Models\jambo\JamboSetting;
use App\Models\jambo\JamboSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JamboController extends Controller
{
    /**
     * PHP 7.4 hardcoded production config
     * No .env dependency
     */
    protected $dashboardPassword = '#jambo_web';
    protected $syncToken = 'JAMBO_SYNC_TOKEN_2026_FIXED_BY_CHATGPT';
    protected $syncRateLimit = 60;
    protected $parseRateLimit = 30;
    protected $exportRateLimit = 10;
    protected $dashboardRateLimit = 20;
    protected $rateWindowSeconds = 60;
    protected $dashboardSessionKey = 'jambo_auth';

    protected function dashboardAllowed(Request $request)
    {
        $configured = (string) $this->dashboardPassword;

        if ($configured === '') {
            return true;
        }

        return (bool) $request->session()->get($this->dashboardSessionKey, false);
    }

    protected function requireDashboardAuth(Request $request)
    {
        if (!$this->dashboardAllowed($request)) {
            abort(403, 'Jambo dashboard locked');
        }
    }

    protected function requestIp(Request $request)
    {
        return (string) $request->ip();
    }

    protected function requestUserAgent(Request $request = null)
    {
        if ($request === null) {
            return '';
        }

        return (string) $request->userAgent();
    }

    protected function getClientKey(Request $request, $token)
    {
        return (string) $token . '|' . $this->requestIp($request);
    }

    protected function getProvidedToken(Request $request)
    {
        $headerToken = (string) $request->header('X-JAMBO-TOKEN', '');
        if ($headerToken !== '') {
            return $headerToken;
        }

        return (string) $request->input('sync_token', '');
    }

    protected function tokenAuthorized(Request $request)
    {
        $token = $this->getProvidedToken($request);

        if ($this->syncToken === '') {
            return true;
        }

        if ($token === '') {
            return false;
        }

        return hash_equals((string) $this->syncToken, (string) $token);
    }

    protected function rateLimit($scope, $clientKey, $limit = 30, $seconds = 60)
    {
        $key = 'jambo:rl:' . $scope . ':' . sha1((string) $clientKey);

        $count = Cache::get($key, 0);
        $count++;

        if ($count === 1) {
            Cache::put($key, 1, $seconds);
        } else {
            Cache::increment($key);
        }

        if ($count > $limit) {
            return array(
                'ok' => false,
                'message' => 'Too many requests',
                'retry_after' => $seconds,
            );
        }

        return null;
    }

    protected function audit($module, $action, array $meta = array(), Request $request = null)
    {
        JamboSyncLog::create(array(
            'module' => (string) $module,
            'payload_count' => 0,
            'saved_count' => 0,
            'duplicate_count' => 0,
            'request_ip' => $request ? $this->requestIp($request) : '',
            'meta_json' => json_encode(array(
                'action' => (string) $action,
                'meta' => $meta,
                'user_agent' => $request ? $this->requestUserAgent($request) : '',
            ), JSON_UNESCAPED_UNICODE),
        ));
    }

    public function login(Request $request)
    {
        $password = (string) $request->input('password', '');

        if ($this->dashboardPassword === '' || hash_equals((string) $this->dashboardPassword, $password)) {
            $request->session()->put($this->dashboardSessionKey, true);
            $this->audit('auth', 'dashboard_login', array(), $request);

            return redirect('/jambo')->with('status', 'Dashboard unlocked.');
        }

        return redirect('/jambo')->with('status', 'Invalid dashboard password.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget($this->dashboardSessionKey);
        $this->audit('auth', 'dashboard_logout', array(), $request);

        return redirect('/jambo')->with('status', 'Logged out.');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $module = trim((string) $request->get('module', ''));
        $locked = !$this->dashboardAllowed($request);

        $stats = array(
            'items' => JamboItem::count(),
            'snippets' => JamboItem::where('module', 'snippets')->count(),
            'notes' => JamboItem::where('module', 'notes')->count(),
            'tasks' => JamboItem::where('module', 'tasks')->count(),
            'income' => (float) JamboItem::where('module', 'ledger')->where('meta_kind', 'income')->sum('amount'),
            'expense' => (float) JamboItem::where('module', 'ledger')->where('meta_kind', 'expense')->sum('amount'),
            'logs' => JamboSyncLog::count(),
        );
        $stats['balance'] = (float) $stats['income'] - (float) $stats['expense'];

        $logs = JamboSyncLog::latest('id')->limit(40)->get();
        $modules = JamboItem::select('module', DB::raw('count(*) as total'))->groupBy('module')->orderByDesc('total')->get();

        $items = collect();

        if (!$locked) {
            $items = JamboItem::query()
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('title', 'like', '%' . $search . '%')
                            ->orWhere('content', 'like', '%' . $search . '%')
                            ->orWhere('module', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%');
                    });
                })
                ->when($module !== '', function ($q) use ($module) {
                    $q->where('module', $module);
                })
                ->latest('id')
                ->paginate(25);
        }

        return view('jambo.index', compact('items', 'stats', 'search', 'module', 'locked', 'logs', 'modules'));
    }

    protected function validatedItem(Request $request)
    {
        return Validator::make($request->all(), array(
            'module' => 'required|string|max:80',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'category' => 'nullable|string|max:120',
            'amount' => 'nullable|numeric',
            'meta_kind' => 'nullable|string|max:120',
            'meta_json' => 'nullable',
            'source' => 'nullable|string|max:80',
        ))->validate();
    }

    public function store(Request $request)
    {
        $this->requireDashboardAuth($request);

        $limited = $this->rateLimit('dashboard_store', $this->requestIp($request), $this->dashboardRateLimit, $this->rateWindowSeconds);
        if ($limited) {
            return redirect('/jambo')->with('status', $limited['message']);
        }

        $data = $this->validatedItem($request);
        $data['external_id'] = isset($data['external_id']) ? (string) $data['external_id'] : (string) Str::uuid();
        $data['fingerprint'] = sha1(json_encode(array(
            $data['module'],
            isset($data['title']) ? $data['title'] : '',
            isset($data['content']) ? $data['content'] : '',
            isset($data['category']) ? $data['category'] : '',
            isset($data['amount']) ? $data['amount'] : '',
            isset($data['meta_kind']) ? $data['meta_kind'] : '',
            time(),
        )));
        $data['source'] = isset($data['source']) && $data['source'] !== '' ? $data['source'] : 'dashboard';

        JamboItem::create($data);
        $this->audit($data['module'], 'dashboard_store', array('title' => isset($data['title']) ? $data['title'] : ''), $request);

        return redirect('/jambo')->with('status', 'Item created.');
    }

    public function edit(Request $request, $id)
    {
        $this->requireDashboardAuth($request);

        $item = JamboItem::findOrFail($id);

        return response()->json(array(
            'ok' => true,
            'item' => $item,
        ));
    }

    public function update(Request $request, $id)
    {
        $this->requireDashboardAuth($request);

        $limited = $this->rateLimit('dashboard_update', $this->requestIp($request), $this->dashboardRateLimit, $this->rateWindowSeconds);
        if ($limited) {
            return redirect('/jambo')->with('status', $limited['message']);
        }

        $item = JamboItem::findOrFail($id);
        $data = $this->validatedItem($request);
        $item->update($data);

        $this->audit(isset($data['module']) ? $data['module'] : $item->module, 'dashboard_update', array('id' => $id), $request);

        return redirect('/jambo')->with('status', 'Item updated.');
    }

    public function delete(Request $request, $id)
    {
        $this->requireDashboardAuth($request);

        $limited = $this->rateLimit('dashboard_delete', $this->requestIp($request), $this->dashboardRateLimit, $this->rateWindowSeconds);
        if ($limited) {
            return redirect('/jambo')->with('status', $limited['message']);
        }

        $item = JamboItem::findOrFail($id);
        $module = $item->module;
        $item->delete();

        $this->audit($module, 'dashboard_delete', array('id' => $id), $request);

        return redirect('/jambo')->with('status', 'Item deleted.');
    }

    public function sync(Request $request)
    {
        $providedToken = $this->getProvidedToken($request);

        $limited = $this->rateLimit('api_sync', $this->getClientKey($request, $providedToken), $this->syncRateLimit, $this->rateWindowSeconds);
        if ($limited) {
            return response()->json($limited, 429);
        }

        if (!$this->tokenAuthorized($request)) {
            $this->audit('auth', 'sync_unauthorized', array(), $request);

            return response()->json(array(
                'ok' => false,
                'message' => 'Unauthorized',
            ), 401);
        }

        $module = (string) $request->input('module', '');
        $payload = $request->input('payload', array());

        if ($module === '') {
            return response()->json(array(
                'ok' => false,
                'message' => 'Module required',
            ), 422);
        }

        $saved = 0;
        $duplicates = 0;

        foreach ((array) $payload as $row) {
            if (!is_array($row)) {
                continue;
            }

            $fingerprint = sha1(json_encode(array(
                $module,
                isset($row['id']) ? $row['id'] : '',
                isset($row['title']) ? $row['title'] : '',
                isset($row['content']) ? $row['content'] : '',
                isset($row['created_at']) ? $row['created_at'] : '',
                isset($row['kind']) ? $row['kind'] : '',
                isset($row['amount']) ? $row['amount'] : '',
            )));

            if (JamboItem::where('fingerprint', $fingerprint)->exists()) {
                $duplicates++;
                continue;
            }

            JamboItem::create(array(
                'module' => $module,
                'external_id' => isset($row['id']) && $row['id'] !== '' ? (string) $row['id'] : (string) Str::uuid(),
                'title' => isset($row['title']) ? (string) $row['title'] : '',
                'content' => isset($row['content']) ? (string) $row['content'] : (isset($row['note']) ? (string) $row['note'] : (isset($row['text']) ? (string) $row['text'] : '')),
                'category' => isset($row['category']) ? (string) $row['category'] : '',
                'amount' => isset($row['amount']) ? (float) $row['amount'] : 0,
                'meta_kind' => isset($row['kind']) ? (string) $row['kind'] : (isset($row['type']) ? (string) $row['type'] : ''),
                'meta_json' => json_encode($row, JSON_UNESCAPED_UNICODE),
                'fingerprint' => $fingerprint,
                'source' => 'extension',
            ));
            $saved++;
        }

        JamboSyncLog::create(array(
            'module' => $module,
            'payload_count' => count((array) $payload),
            'saved_count' => $saved,
            'duplicate_count' => $duplicates,
            'request_ip' => $this->requestIp($request),
            'meta_json' => json_encode(array(
                'action' => 'sync',
                'user_agent' => $this->requestUserAgent($request),
            ), JSON_UNESCAPED_UNICODE),
        ));

        return response()->json(array(
            'ok' => true,
            'message' => 'Sync complete',
            'module' => $module,
            'saved' => $saved,
            'duplicates' => $duplicates,
        ));
    }

    protected function resolveImageUrl($base, $src)
    {
        $src = trim((string) $src);
        if ($src === '') {
            return '';
        }

        if (Str::startsWith($src, array('http://', 'https://'))) {
            return $src;
        }

        $parts = parse_url($base);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'https';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = isset($parts['path']) ? $parts['path'] : '/';

        if (Str::startsWith($src, '//')) {
            return $scheme . ':' . $src;
        }

        if (Str::startsWith($src, '/')) {
            return $scheme . '://' . $host . $port . $src;
        }

        $dir = rtrim(str_replace('\\', '/', dirname($path)), '/');
        if ($dir === '.' || $dir === '\\') {
            $dir = '';
        }

        $full = $scheme . '://' . $host . $port . ($dir !== '' ? $dir . '/' : '/') . ltrim($src, '/');
        $segments = array();

        foreach (explode('/', parse_url($full, PHP_URL_PATH)) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        return $scheme . '://' . $host . $port . '/' . implode('/', $segments);
    }

    public function parseImages(Request $request)
    {
        $providedToken = $this->getProvidedToken($request);

        $limited = $this->rateLimit('api_parse', $this->getClientKey($request, $providedToken), $this->parseRateLimit, $this->rateWindowSeconds);
        if ($limited) {
            return response()->json($limited, 429);
        }

        if (!$this->tokenAuthorized($request)) {
            return response()->json(array(
                'ok' => false,
                'message' => 'Unauthorized',
            ), 401);
        }

        $url = trim((string) $request->input('url', ''));
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(array(
                'ok' => false,
                'message' => 'Valid URL required',
            ), 422);
        }

        try {
            $response = Http::timeout(20)->get($url);
            $html = (string) $response->body();

            preg_match_all('/<img[^>]+src=["\']?([^"\' >]+)["\']?/i', $html, $matches);

            $images = collect($matches[1])->map(function ($src) use ($url) {
                return $this->resolveImageUrl($url, $src);
            })->filter(function ($src) {
                return $src !== '';
            })->unique()->values()->all();

            $this->audit('url_parser', 'parse_images', array('url' => $url, 'count' => count($images)), $request);

            return response()->json(array(
                'ok' => true,
                'url' => $url,
                'count' => count($images),
                'images' => $images,
            ));
        } catch (\Throwable $e) {
            $this->audit('url_parser', 'parse_images_error', array('url' => $url, 'error' => $e->getMessage()), $request);

            return response()->json(array(
                'ok' => false,
                'message' => $e->getMessage(),
            ), 500);
        }
    }

    public function export(Request $request)
    {
        $providedToken = $this->getProvidedToken($request);

        $limited = $this->rateLimit('api_export', $this->getClientKey($request, $providedToken), $this->exportRateLimit, $this->rateWindowSeconds);
        if ($limited) {
            return response()->json($limited, 429);
        }

        if (!$this->tokenAuthorized($request)) {
            $this->audit('auth', 'export_unauthorized', array(), $request);

            return response()->json(array(
                'ok' => false,
                'message' => 'Unauthorized',
            ), 401);
        }

        $module = trim((string) $request->get('module', ''));

        $query = JamboItem::query();
        if ($module !== '') {
            $query->where('module', $module);
        }

        $items = $query->latest('id')->limit(5000)->get();

        $this->audit('export', 'api_export', array(
            'module' => $module,
            'count' => $items->count(),
        ), $request);

        return response()->json(array(
            'ok' => true,
            'module' => $module,
            'count' => $items->count(),
            'items' => $items,
        ));
    }
}
