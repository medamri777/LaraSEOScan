<?php
namespace App\Exports;

use App\Models\SeoScan;
use Maatwebsite\Excel\Concerns\FromArray;

class SeoScanExport implements FromArray
{
    protected $scan;

    public function __construct($id)
    {
        $this->scan = SeoScan::with('pages.links', 'pages.images')->findOrFail($id);
    }

    public function array(): array
    {
        $data = [];

        $data[] = ['Scan Info'];
        $data[] = ['URL', $this->scan->url];
        $data[] = ['Date', $this->scan->created_at];
        $data[] = [''];

        foreach ($this->scan->pages as $page) {
            $data[] = ['Page URL', $page->url];
            $data[] = ['Title', $page->title];
            $data[] = ['Description', $page->description];

            $data[] = ['Links'];
            $data[] = ['URL', 'Status'];
            foreach ($page->links as $link) {
                $data[] = [$link->href, $link->status_code];
            }

            $data[] = ['Images'];
            $data[] = ['Image URL', 'Alt Text'];
            foreach ($page->images as $img) {
                $data[] = [$img->src, $img->alt];
            }

            $data[] = ['']; // Spacer row between pages
        }

        return $data;
    }
}
