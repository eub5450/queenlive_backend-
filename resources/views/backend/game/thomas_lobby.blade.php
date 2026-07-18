@extends('backend.layouts.main')

@section('title')
Thomas Game Lobby
@endsection

@section('content')
<style>
    .thomas-lobby-page{padding:24px;background:#f3f6fb;min-height:calc(100vh - 70px)}
    .thomas-hero{position:relative;overflow:hidden;border-radius:26px;padding:26px 28px;background:linear-gradient(135deg,#071321,#10264a 52%,#441a66);color:#fff;box-shadow:0 20px 45px rgba(4,15,34,.18);margin-bottom:22px}
    .thomas-hero:before{content:"";position:absolute;right:-90px;top:-110px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(0,217,255,.34),rgba(0,217,255,0) 68%)}
    .thomas-hero:after{content:"";position:absolute;left:38%;bottom:-130px;width:360px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(255,214,110,.24),rgba(255,214,110,0) 65%)}
    .thomas-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
    .thomas-title{font-size:34px;line-height:1.08;margin:0;font-weight:900;color:#fff}
    .thomas-actions{display:flex;gap:10px;flex-wrap:wrap}
    .thomas-btn{border:0;border-radius:999px;padding:11px 16px;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;gap:7px;box-shadow:0 10px 24px rgba(0,0,0,.14);cursor:pointer}
    .thomas-btn-primary{background:linear-gradient(135deg,#16d5ff,#6d5dfc);color:#fff}
    .thomas-btn-dark{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)}
    .thomas-btn-live{background:linear-gradient(135deg,#11c881,#06a767);color:#fff}
    .thomas-btn-off{background:linear-gradient(135deg,#ff6b6b,#d6254c);color:#fff}
    .thomas-summary{display:grid;grid-template-columns:repeat(4,minmax(150px,1fr));gap:14px;margin-bottom:14px}
    .thomas-stat{background:#fff;border:1px solid #e4eaf5;border-radius:18px;padding:18px;box-shadow:0 12px 28px rgba(22,36,66,.08)}
    .thomas-stat span{display:block;color:#68738a;font-size:12px;text-transform:uppercase;letter-spacing:1px;font-weight:800}
    .thomas-stat strong{display:block;color:#121827;font-size:30px;line-height:1.1;margin-top:8px}
    .thomas-family-summary{display:flex;flex-wrap:wrap;gap:10px;margin:0 0 20px}
    .thomas-family-chip{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff;border:1px solid #dce7f7;color:#16396e;font-size:12px;font-weight:900;box-shadow:0 10px 24px rgba(22,36,66,.08)}
    .thomas-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
    .thomas-section{margin-top:22px}
    .thomas-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 12px;padding:0 2px}
    .thomas-section-title{margin:0;color:#121827;font-size:22px;font-weight:900}
    .thomas-section-count{display:inline-flex;align-items:center;border-radius:999px;padding:7px 12px;background:#fff;color:#315a96;border:1px solid #dce7f7;font-weight:900;font-size:12px}
    .thomas-card{background:#fff;border:1px solid #e4eaf5;border-radius:24px;overflow:hidden;box-shadow:0 14px 36px rgba(22,36,66,.1);display:flex;flex-direction:column}
    .thomas-banner{aspect-ratio:16/9;min-height:190px;position:relative;background:#0d1424;display:flex;align-items:center;justify-content:center;color:#fff;text-align:center}
    .thomas-banner img{width:100%;height:100%;object-fit:contain;object-position:center;display:block;background:#0d1424}
    .thomas-banner-placeholder{width:100%;height:100%;background:#0d1424}
    .thomas-card-body{padding:12px;display:flex;flex-direction:column;gap:0;flex:1}
    .thomas-pill{position:absolute;right:10px;top:10px;z-index:2;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:900;white-space:nowrap;box-shadow:0 8px 22px rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.7)}
    .thomas-pill-live{background:#ddfff1;color:#047a4c}
    .thomas-pill-developer{background:#fff7d7;color:#8a5a00}
    .thomas-pill-maintenance{background:#fff0f3;color:#b3163a}
    .thomas-card-copy{display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
    .thomas-card-title{margin:0;color:#121827;font-size:18px;font-weight:900}
    .thomas-card-meta{color:#5d6982;font-size:12px;font-weight:800}
    .thomas-card-note{color:#3a475e;font-size:12px;line-height:1.4;font-weight:700}
    .thomas-card-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin-top:auto}
    .thomas-card-actions form{margin:0}
    .thomas-card-actions .thomas-btn{width:100%;justify-content:center;min-height:42px}
    .thomas-preview-btn{background:linear-gradient(135deg,#ffb703,#fb7185);color:#fff}
    .thomas-details-btn{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff}
    .thomas-preview-btn[disabled]{opacity:.55;cursor:not-allowed}
    .thomas-switch-btn{gap:6px;padding-left:10px;padding-right:10px}
    .thomas-switch-dot{width:13px;height:13px;border-radius:50%;display:inline-block;background:rgba(255,255,255,.92);box-shadow:0 0 0 3px rgba(255,255,255,.18)}
    .thomas-switch-text{font-size:12px;line-height:1}
    .thomas-btn[disabled]{filter:grayscale(.1);opacity:.68;cursor:not-allowed}
    .thomas-alert{border-radius:16px;padding:14px 16px;margin-bottom:18px;font-weight:700}
    .thomas-alert-success{background:#eafff5;color:#056f46;border:1px solid #b8f2d9}
    .thomas-alert-error{background:#fff0f3;color:#a81033;border:1px solid #ffd0da}
    .thomas-foot{margin-top:22px;text-align:center;color:#8a93a5;font-size:10px;font-weight:800}
    .thomas-preview-modal{position:fixed;right:24px;top:88px;bottom:24px;z-index:9999;display:none;width:min(430px,calc(100vw - 32px));pointer-events:none}
    .thomas-preview-modal.is-open{display:block}
    .thomas-preview-panel{height:100%;border-radius:34px;padding:14px;background:linear-gradient(145deg,#151b2f,#020711);box-shadow:0 32px 110px rgba(0,0,0,.42);border:1px solid rgba(255,255,255,.16);pointer-events:auto}
    .thomas-preview-top{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:4px 5px 12px;color:#fff}
    .thomas-preview-tools{display:flex;align-items:center;gap:8px}
    .thomas-preview-tools button{border:0;border-radius:999px;width:34px;height:34px;font-size:20px;line-height:1;font-weight:900;color:#fff;text-decoration:none;background:#ff426a;cursor:pointer}
    .thomas-phone{position:relative;margin:auto;width:100%;height:calc(100% - 50px);border-radius:42px;background:#05070d;padding:16px 10px 14px;border:2px solid #2b3248;box-shadow:inset 0 0 0 4px #0d1220}
    .thomas-phone:before{content:"";position:absolute;top:8px;left:50%;transform:translateX(-50%);width:118px;height:24px;border-radius:0 0 16px 16px;background:#05070d;z-index:2;border:1px solid #171c2a}
    .thomas-phone iframe{width:100%;height:100%;border:0;border-radius:30px;background:#111827;display:block}
    .thomas-preview-empty{display:flex;align-items:center;justify-content:center;height:100%;border-radius:30px;background:#101827;color:#d8e8ff;text-align:center;padding:18px;font-weight:800}
    @media(max-width:1100px){.thomas-summary{grid-template-columns:repeat(2,minmax(150px,1fr))}}
    @media(max-width:720px){.thomas-lobby-page{padding:14px}.thomas-title{font-size:26px}.thomas-summary{grid-template-columns:1fr}.thomas-card-actions{grid-template-columns:1fr}.thomas-preview-modal{right:10px;top:74px;bottom:10px;width:calc(100vw - 20px)}}
</style>

<div class="body-content thomas-lobby-page">
    <div class="thomas-hero">
        <div class="thomas-hero-content">
            <div>
                <h1 class="thomas-title">Thomas Game Lobby</h1>
            </div>
        </div>
    </div>

    @if(session('messege'))
        <div class="thomas-alert thomas-alert-success">{{ session('messege') }}</div>
    @endif
    @if($errorMessage)
        <div class="thomas-alert thomas-alert-error">{{ $errorMessage }}</div>
    @endif
    <div class="thomas-summary">
        <div class="thomas-stat"><span>{{ $balanceSummary['label'] ?? 'Thomas Game Balance' }}</span><strong>{{ $balanceSummary['formatted'] ?? '0.00' }}</strong></div>
        <div class="thomas-stat"><span>Total Game</span><strong>{{ $summary['total'] ?? 0 }}</strong></div>
        <div class="thomas-stat"><span>All User ON</span><strong>{{ $summary['live'] ?? 0 }}</strong></div>
        <div class="thomas-stat"><span>OFF ID Only</span><strong>{{ $summary['developer'] ?? 0 }}</strong></div>
    </div>
    @if(!empty($familySummary))
        <div class="thomas-family-summary">
            @foreach($familySummary as $family)
                <div class="thomas-family-chip">{{ $family['label'] }} <strong>{{ $family['count'] }}</strong></div>
            @endforeach
        </div>
    @endif

    @if($games->isEmpty())
        <div class="thomas-alert thomas-alert-error">No Thomas game rooms found.</div>
    @else
        @php
            $gameSections = [
                'All User Live Rooms' => $games->where('status_key', 'live'),
                'OFF ID Only Rooms' => $games->where('status_key', 'developer'),
                'OFF Rooms' => $games->where('status_key', 'maintenance'),
            ];
        @endphp

        @foreach($gameSections as $sectionTitle => $sectionGames)
            @if($sectionGames->count() > 0)
                <div class="thomas-section">
                    <div class="thomas-section-head">
                        <h2 class="thomas-section-title">{{ $sectionTitle }}</h2>
                        <span class="thomas-section-count">{{ $sectionGames->count() }} Games</span>
                    </div>
                    <div class="thomas-grid">
                        @foreach($sectionGames as $game)
                            <div class="thomas-card">
                                <div class="thomas-banner">
                                    <span class="thomas-pill {{
                                        $game['status_key'] === 'live'
                                            ? 'thomas-pill-live'
                                            : ($game['status_key'] === 'developer' ? 'thomas-pill-developer' : 'thomas-pill-maintenance')
                                    }}">{{ $game['status_label'] }}</span>
                                    @if(!empty($game['banner']))
                                        <img src="{{ $game['banner'] }}" alt="{{ $game['name'] }} banner" onerror="this.style.display='none'; this.parentNode.querySelector('.thomas-banner-placeholder').style.display='block';">
                                        <div class="thomas-banner-placeholder" style="display:none"></div>
                                    @else
                                        <div class="thomas-banner-placeholder"></div>
                                    @endif
                                </div>
                                <div class="thomas-card-body">
                                    <div class="thomas-card-copy">
                                        <h3 class="thomas-card-title">{{ $game['name'] }}</h3>
                                        <div class="thomas-card-meta">{{ $game['family_label'] }} | {{ $game['game_code'] }}</div>
                                        <div class="thomas-card-note">{{ $game['access_label'] }}</div>
                                        @if(!empty($game['maintenance_message']))
                                            <div class="thomas-card-note">{{ $game['maintenance_message'] }}</div>
                                        @endif
                                    </div>
                                    <div class="thomas-card-actions">
                                        <button
                                            class="thomas-btn thomas-preview-btn js-thomas-preview"
                                            type="button"
                                            data-title="{{ e($game['name']) }}"
                                            data-url="{{ e($game['preview_url']) }}"
                                            {{ empty($game['preview_url']) ? 'disabled' : '' }}>
                                            Preview
                                        </button>
                                        @if(!empty($game['enabled']))
                                            <a class="thomas-btn thomas-details-btn" href="{{ $game['details_url'] }}">
                                                Details
                                            </a>
                                        @else
                                            <button class="thomas-btn thomas-details-btn" type="button" disabled>
                                                Details
                                            </button>
                                        @endif
                                        <form action="{{ URL::to('admin/thomas-game-lobby/status') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="game_code" value="{{ $game['game_code'] }}">
                                            @if($game['status_key'] === 'live')
                                                <input type="hidden" name="mode" value="developer">
                                                <button class="thomas-btn thomas-btn-off thomas-switch-btn" type="submit" title="OFF keeps this open only for IDs 1111,22222">
                                                    <span class="thomas-switch-dot"></span>
                                                    <span class="thomas-switch-text">OFF ID</span>
                                                </button>
                                            @else
                                                <input type="hidden" name="mode" value="live">
                                                <button class="thomas-btn thomas-btn-live thomas-switch-btn" type="submit" title="ON opens this game for all users">
                                                    <span class="thomas-switch-dot"></span>
                                                    <span class="thomas-switch-text">ON Live</span>
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    <div class="thomas-foot">Powerd by JAMBOai</div>
</div>

<div class="thomas-preview-modal" id="thomasPreviewModal" aria-hidden="true">
    <div class="thomas-preview-panel">
        <div class="thomas-preview-top">
            <div class="thomas-preview-tools">
                <button type="button" id="thomasPreviewClose" aria-label="Close preview">&times;</button>
            </div>
        </div>
        <div class="thomas-phone">
            <iframe id="thomasPreviewFrame" src="about:blank" title="Thomas game preview" loading="lazy"></iframe>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('thomasPreviewModal');
        var frame = document.getElementById('thomasPreviewFrame');
        var closeBtn = document.getElementById('thomasPreviewClose');

        function closePreview() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            frame.setAttribute('src', 'about:blank');
        }

        document.querySelectorAll('.js-thomas-preview').forEach(function (button) {
            button.addEventListener('click', function () {
                var url = button.getAttribute('data-url') || '';
                if (!url) {
                    return;
                }
                frame.setAttribute('src', url);
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        closeBtn.addEventListener('click', closePreview);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closePreview();
            }
        });
    })();
</script>
@endsection
