
<?php

$domain = Context::getInstance()->getFront()->getRequest()->getDomain() . '/';


?>
<div class="col-md-9">
    <?php
    if (isset($this->data) && !empty($this->data)):
        $category = $this->data['category'];
        ?>
        <h4> <?php echo $category['category_name'] ?></h4>

        <?php foreach ($this->data["news"] as $item): ?>
        <div class="new-item">
            <div class="row">
                <div class="col-md-4">
                    <a href="<?= $domain.$item['news_link'] ?> "><img src="<?= $item['news_image'] ?>"
                                                              alt="" class="img-fluid"></a>
                </div>
                <div class="col-md-8 text-justify">
                    <a href="<?= $domain.$item['news_link'] ?>" class="news_title"><?= $item['news_title'] ?></a>
                    <p><?= $item['news_seo_title'] ?></p>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
        <hr>

    <?php endif; ?>
    <?php
//        var_dump(rtrim($this->pageUrl));
    echo Util::paginate(1000, $this->pageIndex, $domain.$this->pageUrl , 'paging', 'trang-');
    ?>
</div>

