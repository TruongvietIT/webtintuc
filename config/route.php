<?php

return array(
    'default' => array(
        '/' => 'Home',
        'ajax' => 'Ajax',
        'sitemap.xml' => 'Sitemap',
        'sitemaps/<slug:[\w\d-]+>.xml' => 'SitemapDetail',
        'managecache' => 'ManageCache',
        'tag/<slug:[\w\d-]+>(?:/trang-<page:\d+>)?' => 'Tag',
        'su-kien(?:/trang-<page:\d+>)?' => 'Event',
        '<id:\d+>(?:/<slug:[\w\d-]+>)?(?:/trang-<page:\d+>)?' => 'EventDetail',
        '<slug:[\w\d-]+>-<id:\d+>.html' => 'NewsDetail',
        'tin-moi-nhat(?:/trang-<page:\d+$>)?' => 'Newest',
        'tag/<slug:[\w\d-]+>(?:/trang-<page:\d+>)?' => 'Tag',
        '<slug:[a-z\d-]+>(?:/trang-<page:\d+$>)?' => 'Category',
        'default' => 'Notfound'
    ),
    'mobile' => array('/' => 'Home',
        'ajax' => 'Ajax',
        'tag/<slug:[\w\d-]+>(?:/trang-<page:\d+>)?' => 'Tag',
        '<slug:[\w\d-]+>-<id:\d+>.html' => 'NewsDetail',
        'default' => 'Home'
    )
);
