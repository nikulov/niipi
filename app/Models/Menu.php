<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';
    
    protected $fillable = [
        'top_items',
        'footer_items',
    ];
    
    protected $casts = [
        'top_items' => 'array',
        'footer_items' => 'array',
    ];
    
    public static function getTopMenuItems(): array
    {
        $menuItems = self::query()->select('top_items')->first()?->top_items ?? [];
        
        return self::normalizeMenuItems($menuItems);
    }
    
    
    public static function getFooterMenuItems()
    {
        $menuItems = self::query()->select('footer_items')->first()?->footer_items ?? [];
        
        return self::normalizeMenuItems($menuItems);
    }
    
    protected static function normalizeMenuItems(array $items): array
    {
        return array_map(function(array $item) {
            $type = $item['type'] ?? null;
            $href = null;
            
            if ($type === 'custom') {
                $url = $item['url'] ?? null;
                
                if ($url && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
                    $href = $url;
                } else {
                    $href = '/' . ltrim($url ?? '', '/');
                }
            }
            
            if ($type === 'page') {
                $slug = $item['page_slug'] ?? null;
                
                if ($slug === 'home' || $slug === '/home' || $slug === null) {
                    $href = '/';
                } else {
                    $slug = ltrim($slug, '/');
                    $href = '/' . $slug;
                }
            }
            
            $children = !empty($item['children'])
                ? self::normalizeMenuItems($item['children'])
                : [];
            
            return [
                'href' => $href,
                'label' => $item['label'] ?? null,
                'blank' => (bool)($item['blank'] ?? false),
                'children' => $children,
            ];
        }, $items);
    }
}

