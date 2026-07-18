<?php
// app/Helpers/JsonLogger.php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class JsonLogger
{
    private $logFile;
    private $config;
    private $data;
    
    public function __construct($config = [])
    {
        $this->config = array_merge([
            'name' => 'default',
            'path' => storage_path('logs/json'),
            'max_entries' => 100,
            'keep_errors' => true
        ], $config);
        
        $this->logFile = $this->config['path'] . '/' . $this->config['name'] . '.json';
        $this->ensureFile();
        $this->load();
    }
    
    private function ensureFile()
    {
        $dir = $this->config['path'];
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        
        if (!File::exists($this->logFile)) {
            File::put($this->logFile, json_encode([
                'config' => [
                    'name' => $this->config['name'],
                    'created_at' => date('Y-m-d H:i:s')
                ],
                'stats' => [
                    'total_entries' => 0,
                    'total_errors' => 0,
                    'last_entry' => null
                ],
                'tables' => [],
                'errors' => []
            ], JSON_PRETTY_PRINT));
        }
    }
    
    private function load()
    {
        $this->data = json_decode(File::get($this->logFile), true);
    }
    
    private function save()
    {
        $this->data['stats']['last_entry'] = date('Y-m-d H:i:s');
        $this->data['stats']['total_entries'] = $this->countEntries();
        File::put($this->logFile, json_encode($this->data, JSON_PRETTY_PRINT));
    }
    
    private function countEntries()
    {
        $count = count($this->data['errors']);
        foreach ($this->data['tables'] as $table) {
            $count += count($table['data']);
        }
        return $count;
    }
    
    public function table($name, $columns = [])
    {
        if (!isset($this->data['tables'][$name])) {
            $this->data['tables'][$name] = [
                'name' => $name,
                'columns' => $columns,
                'data' => []
            ];
        }
        return new JsonTableHelper($this, $name);
    }
    
    public function addEntry($table, $row)
    {
        if (!isset($this->data['tables'][$table])) {
            return false;
        }
        
        $row['timestamp'] = date('Y-m-d H:i:s');
        array_unshift($this->data['tables'][$table]['data'], $row);
        
        $this->data['tables'][$table]['data'] = array_slice(
            $this->data['tables'][$table]['data'],
            0,
            $this->config['max_entries']
        );
        
        $this->save();
        return true;
    }
    
    public function addError($error)
    {
        $error['timestamp'] = date('Y-m-d H:i:s');
        $error['id'] = uniqid('err_');
        
        array_unshift($this->data['errors'], $error);
        $this->data['stats']['total_errors'] = count($this->data['errors']);
        
        if ($this->config['keep_errors']) {
            $this->data['errors'] = array_slice($this->data['errors'], 0, 100);
        }
        
        $this->save();
        Log::error($error['subject'] . ': ' . $error['message']);
        
        return $error['id'];
    }
    
    public function addStats($stats)
    {
        if (!isset($this->data['stats']['daily'])) {
            $this->data['stats']['daily'] = [];
        }
        
        $today = date('Y-m-d');
        $this->data['stats']['daily'][$today] = $stats;
        $this->data['stats']['daily'] = array_slice($this->data['stats']['daily'], -30, null, true);
        
        $this->save();
        return true;
    }
    
    public function getAll()
    {
        return $this->data;
    }
}

class JsonTableHelper
{
    private $logger;
    private $table;
    
    public function __construct($logger, $table)
    {
        $this->logger = $logger;
        $this->table = $table;
    }
    
    public function add($row)
    {
        $this->logger->addEntry($this->table, $row);
        return $this;
    }
    
    public function addMany($rows)
    {
        foreach ($rows as $row) {
            $this->logger->addEntry($this->table, $row);
        }
        return $this;
    }
}