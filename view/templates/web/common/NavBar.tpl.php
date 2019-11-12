<div class="col-md-3">
    <?php $domain = Context::getInstance()->getFront()->getRequest()->getDomain() . '/'; ?>
    <!--    --><?php //var_dump($this->data); ?>
    <h4>Tin xem nhiều nhất</h4>
    <?php foreach ($this->data as $item): ?>
        <div class="row" style="margin-bottom: 20px">
            <div class="col-md-5">
                <a href="<?= $domain . $item['news_link'] ?>">
                    <img src="<?= $item['news_image'] ?>"
                         alt="" class="img-fluid">
                </a>
            </div>

            <div class="col-md-7 text-justify">
                <a style="font-size: 14px" href="<?= $domain . $item['news_link'] ?>"
                   class="news_title"><?= $item['news_title'] ?></a>
            </div>
        </div>

    <?php endforeach; ?>


</div>
