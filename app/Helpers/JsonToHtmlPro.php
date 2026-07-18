<?php
// app/Helpers/JsonToHtmlPro.php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class JsonToHtmlPro
{
    private $jsonFile;
    private $htmlFile;
    private $data;
    
    public function __construct($jsonFile)
    {
        $this->jsonFile = storage_path('logs/json/' . $jsonFile);
        $this->htmlFile = public_path('log-viewer/' . str_replace('.json', '.html', $jsonFile));
        $this->load();
    }
    
    private function load()
    {
        if (!File::exists($this->jsonFile)) {
            $this->data = [
                'config' => ['name' => 'New Log'],
                'stats' => ['total_entries' => 0],
                'tables' => [],
                'errors' => []
            ];
        } else {
            $this->data = json_decode(File::get($this->jsonFile), true);
        }
    }
    
    public function generate()
    {
        $html = $this->getTemplate();
        File::put($this->htmlFile, $html);
        return $this;
    }
    
    public function getUrl()
    {
        return url('log-viewer/' . basename($this->htmlFile));
    }
    
    private function getTemplate()
    {
        $name = $this->data['config']['name'] ?? 'Log Viewer';
        $stats = $this->data['stats'] ?? [];
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>📊 ' . $name . '</title>
    <meta http-equiv="refresh" content="10">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            background:linear-gradient(135deg,#0f0c1f,#1a1730);
            font-family:"Segoe UI",sans-serif;
            padding:30px;
            color:#e0e0e0;
        }
        .container{max-width:1400px;margin:0 auto;}
        .header{
            background:rgba(20,15,40,0.95);
            border-radius:16px;
            padding:25px;
            margin-bottom:30px;
        }
        h1{
            background:linear-gradient(135deg,#a78bfa,#ec4899);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
        }
        .stats{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
            gap:20px;
            margin:20px 0;
        }
        .stat-card{
            background:rgba(20,15,40,0.7);
            border-radius:12px;
            padding:20px;
        }
        table{
            width:100%;
            border-collapse:collapse;
            background:rgba(20,15,40,0.7);
            margin:20px 0;
            border-radius:12px;
            overflow:hidden;
        }
        th{
            background:#8b5cf6;
            color:white;
            padding:15px;
            text-align:left;
        }
        td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.05);}
        .error-row td{color:#f87171;}
        .error-code{
            background:#ef4444;
            color:white;
            padding:3px 8px;
            border-radius:12px;
            font-size:11px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 ' . $name . '</h1>
            <div class="stats">
                <div class="stat-card">Total Entries: ' . ($stats['total_entries'] ?? 0) . '</div>
                <div class="stat-card">Total Errors: ' . ($stats['total_errors'] ?? 0) . '</div>
                <div class="stat-card">Last Update: ' . date('H:i:s') . '</div>
            </div>
        </div>';
        
        // Add tables
        if (!empty($this->data['tables'])) {
            foreach ($this->data['tables'] as $tableName => $table) {
                $html .= '<h2>' . $tableName . '</h2>';
                $html .= '<table><thead><tr>';
                
                if (!empty($table['columns'])) {
                    foreach ($table['columns'] as $col) {
                        $html .= '<th>' . $col . '</th>';
                    }
                }
                
                $html .= '</tr></thead><tbody>';
                
                if (!empty($table['data'])) {
                    foreach (array_slice($table['data'], 0, 50) as $row) {
                        $html .= '<tr>';
                        foreach ($table['columns'] as $col) {
                            $key = strtolower(str_replace(' ', '_', $col));
                            $value = $row[$key] ?? $row[$col] ?? '-';
                            $html .= '<td>' . $value . '</td>';
                        }
                        $html .= '</tr>';
                    }
                }
                
                $html .= '</tbody></table>';
            }
        }
        
        // Add errors
        if (!empty($this->data['errors'])) {
            $html .= '<h2 style="color:#ef4444;">❌ Errors</h2>';
            $html .= '<table class="error-table"><thead><tr><th>Time</th><th>Code</th><th>Subject</th><th>Message</th></tr></thead><tbody>';
            
            foreach (array_slice($this->data['errors'], 0, 20) as $error) {
                $html .= '<tr class="error-row">';
                $html .= '<td>' . ($error['timestamp'] ?? '') . '</td>';
                $html .= '<td><span class="error-code">' . ($error['code'] ?? '') . '</span></td>';
                $html .= '<td>' . ($error['subject'] ?? '') . '</td>';
                $html .= '<td>' . ($error['message'] ?? '') . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        $html .= '</div></body></html>';
        
        return $html;
    }
}