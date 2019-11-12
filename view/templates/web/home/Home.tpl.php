<div class="col-md-9">
    <h4> Tin mới nhất</h4>

    <?php foreach ($this->newsNew as $item): ?>
        <div class="new-item">
            <div class="row">
                <div class="col-md-4">
                    <a href="<?= $item['news_link'] ?> "><img src="<?= $item['news_image'] ?>"
                                                              alt="" class="img-fluid"></a>
                </div>
                <div class="col-md-8 text-justify">
                    <a href="<?= $item['news_link'] ?>" class="news_title"><?= $item['news_title'] ?></a>
                    <p><?= $item['news_seo_title'] ?></p>
                    <span class="tie-date">
                        <i class="fa fa-clock-o"></i> <?php echo date("d/m/Y", $item['news_published_date']) ?></span>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
    <hr>

</div>



