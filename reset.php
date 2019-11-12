<?php

$basePath = str_replace('\\', '/', dirname(__FILE__)) . '/';
set_time_limit(0);
ini_set('display_errors', 1);
ini_set("log_errors", "1");
ini_set("error_log", $basePath . "log/errors.log");

include $basePath . 'lib/Transfer.php';
include $basePath . 'lib/cache/MemCached.php';
include $basePath . 'lib/Util.php';
include $basePath . 'lib/Context.php';
include $basePath . 'lib/Helper.php';
include $basePath . 'lib/database/Mysql.php';
include $basePath . 'models/Model.php';
include $basePath . 'models/ResetModel.php';

$newsModel = new ResetModel();
$newsModel->setCacheActive(false);

// reset cache trang chu
$newsModel->setNewsIds();
$newsModel->getHomeHighlightNews();
$newsModel->setNewsIds();
$newsModel->getHomeCategoryNews();
//reset category
$newsModel->setNewsIds();
$categories = $newsModel->getHeaderCategory();
foreach ($categories as $category) {
    $newsModel->setNewsIds();
    $newsModel->getCategoryNews(array('category_slug' => $category['category_slug']), 1);
    $newsModel->setNewsIds();
    $newsModel->getCategoryNews(array('category_slug' => $category['category_slug']), 2);
    $newsModel->setNewsIds();
    $newsModel->getNews(array('status' => 1, 'others' => 1, 'category_id' => $category['category_id']), 1, 6);
}

$newsModel->setNewsIds();
$newsModel->getNews(array('status' => 1, 'others' => 1, 'position_id' => 1), 1, 10);
//reset chân bài viết
$newsModel->setNewsIds();
$newsModel->getNews(array('status' => 1, 'others' => 1), 1, 8);
$newsModel->setNewsIds();
$newsModel->getNews(array('status' => 1, 'others' => 1), 1, 6);
$newsModel->setNewsIds();

$newsModel->getNews(array('status' => 1, 'others' => 0, 'order_by' => 'visit_count', '2day' => '1'), 1, 2);
//reset random news Youtube
$categoryYoutubeId = Helper::getInstance()->getConfig('categoryYoutubeId');
$newsModel->setNewsIds();
$newsModel->getNews(['category_id' => $categoryYoutubeId, 'status' => 1, 'others' => 0], 1, 10);
$newsModel->setNewsIds();
$newsModel->getCategoryNews(array('category_slug' => 'video-danh-cho-tre-em'), 1);
