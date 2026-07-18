<?php
// app/Console/Commands/GenerateLogHtml.php

namespace App\Console\Commands;

use App\Helpers\JsonToHtmlPro;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateLogHtml extends Command
{
    protected $signature = 'log:generate-html {file?}';
    protected $description = 'Generate HTML from JSON logs';

    public function handle()
    {
        $jsonDir = storage_path('logs/json');
        
        if (!File::exists($jsonDir)) {
            $this->error('JSON directory not found');
            return 1;
        }
        
        $files = $this->argument('file') 
            ? [$this->argument('file')]
            : File::files($jsonDir);
        
        $count = 0;
        foreach ($files as $file) {
            $filename = is_string($file) ? $file : $file->getFilename();
            
            if (pathinfo($filename, PATHINFO_EXTENSION) != 'json') {
                continue;
            }
            
            $this->info("Generating HTML for: {$filename}");
            
            $generator = new JsonToHtmlPro($filename);
            $generator->generate();
            
            $count++;
        }
        
        $this->info("Generated {$count} HTML files");
        return 0;
    }
}