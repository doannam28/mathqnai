<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        $idx = $request->get('idx');

        // Nếu chưa có idx => tạo sitemap index
        if (!isset($idx)) {
            $xml = $this->generateSitemapIndex();
        } else {
            $xml = $this->generateSitemapDetail($idx);
        }

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Sitemap index (liệt kê các sitemap con)
     */
    protected function generateSitemapIndex()
    {
        $baseUrl = URL::to('/');
        $now = now()->toAtomString();

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        // Sitemap chính (idx = 0)
        $xml .= "  <sitemap>\n";
        $xml .= "    <loc>{$baseUrl}/sitemap.xml?idx=0</loc>\n";
        $xml .= "    <lastmod>{$now}</lastmod>\n";
        $xml .= "  </sitemap>\n";

        // Sitemap cho từng category
        $cats = Category::where('status', 1)->get();
        foreach ($cats as $cat) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>{$baseUrl}/sitemap.xml?idx={$cat->id}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= "</sitemapindex>";
        return $xml;
    }

    /**
     * Sitemap chi tiết (theo idx)
     */
    protected function generateSitemapDetail($idx)
    {
        $baseUrl = URL::to('/');
        $now = now()->toAtomString();

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        // Trang chủ
        $xml .= $this->urlTag("{$baseUrl}/", $now, 'daily', '1.0');

        if ($idx == 0) {
            // Trang /du-doan-xo-so
            //$xml .= $this->urlTag("{$baseUrl}/du-doan-xo-so", $now, 'daily', '1.0');

            // Các trang Page
            $pages = Page::where('status', 1)->get();
            foreach ($pages as $page) {
                $xml .= $this->urlTag("{$baseUrl}/du-doan-xo-so-{$page->slug}", $now, 'daily', '0.9');
            }
        } else {
            $cat = Category::find($idx);
            if ($cat) {
                // Trang category
                $xml .= $this->urlTag("{$baseUrl}/{$cat->slug}", $now, 'daily', '1.0');

                // Bài viết trong category
                $posts = Post::where('status', 1)->where('parent_id', $idx)->get();
                foreach ($posts as $post) {
                    $xml .= $this->urlTag("{$baseUrl}/{$cat->slug}/{$post->slug}", $now, 'daily', '0.8');
                }
            }
        }

        $xml .= "</urlset>";
        return $xml;
    }

    /**
     * Tạo 1 thẻ <url> trong sitemap
     */
    protected function urlTag($loc, $lastmod, $changefreq = 'daily', $priority = '0.8')
    {
        return "  <url>\n"
            . "    <loc>{$loc}</loc>\n"
            . "    <lastmod>{$lastmod}</lastmod>\n"
            . "    <changefreq>{$changefreq}</changefreq>\n"
            . "    <priority>{$priority}</priority>\n"
            . "  </url>\n";
    }
}
