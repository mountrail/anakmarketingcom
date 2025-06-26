<?php
// app/Models/Sitemap.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap as SpatieSitemap;
use Spatie\Sitemap\Tags\Url;

class Sitemap extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'filename',
        'is_active',
        'priority',
        'changefreq',
        'custom_urls',
        'last_generated',
    ];

    protected $casts = [
        'custom_urls' => 'array',
        'is_active' => 'boolean',
        'last_generated' => 'datetime',
        'priority' => 'decimal:1',
    ];

    public function generateSitemap()
    {
        $sitemap = SpatieSitemap::create();

        switch ($this->type) {
            case 'posts':
                foreach (Post::published()->get() as $post) {
                    $sitemap->add(
                        Url::create("/{$post->slug}")
                            ->setLastModificationDate($post->updated_at)
                            ->setChangeFrequency($this->changefreq)
                            ->setPriority($this->priority)
                    );
                }
                break;

            case 'users':
                foreach (User::whereNotNull('name')->get() as $user) {
                    $sitemap->add(
                        Url::create("/profile/{$user->id}")
                            ->setLastModificationDate($user->updated_at)
                            ->setChangeFrequency($this->changefreq)
                            ->setPriority($this->priority)
                    );
                }
                break;

            case 'static':
                $staticPages = [
                    '/' => ['priority' => 1.0, 'changefreq' => 'daily'],
                    '/pertanyaan' => ['priority' => 0.9, 'changefreq' => 'hourly'],
                    '/diskusi' => ['priority' => 0.9, 'changefreq' => 'hourly'],
                ];

                foreach ($staticPages as $url => $config) {
                    $sitemap->add(
                        Url::create($url)
                            ->setChangeFrequency($config['changefreq'])
                            ->setPriority($config['priority'])
                    );
                }
                break;

            case 'custom':
                if ($this->custom_urls) {
                    foreach ($this->custom_urls as $urlData) {
                        $sitemap->add(
                            Url::create($urlData['url'])
                                ->setChangeFrequency($urlData['changefreq'])
                                ->setPriority($urlData['priority'])
                        );
                    }
                }
                break;
        }

        // Save to public/sitemaps directory
        $path = public_path("sitemaps/{$this->filename}");
        $renderedXml = $sitemap->render();

        // Add XSL stylesheet reference
        $xmlWithStyle = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlWithStyle .= '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>' . "\n";
        $xmlWithStyle .= preg_replace('/^<\?xml[^>]*\?>\s*/', '', $renderedXml);

        file_put_contents($path, $xmlWithStyle);

        $this->update(['last_generated' => now()]);

        return $this;
    }

    public function getUrlAttribute()
    {
        return url("sitemaps/{$this->filename}");
    }
}
