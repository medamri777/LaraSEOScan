<table>
    <thead>
        <tr>
            <th>Page URL</th>
            <th>Links</th>
            <th>Images</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($scan->pages as $page)
            <tr>
                <td>{{ $page->url }}</td>
                <td>
                    @foreach ($page->links as $link)
                        {{ $link->url }} ({{ $link->status_code }})<br>
                    @endforeach
                </td>
                <td>
                    @foreach ($page->images as $image)
                        {{ $image->src }} (alt: {{ $image->alt ?? 'N/A' }})<br>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
