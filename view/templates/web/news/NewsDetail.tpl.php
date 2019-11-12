<?php
//var_dump($this->data);die('111111111');
//$news = (isset($this->news[0]) ? $this->news[0] : false);
//var_dump($news);
?>
<div class="col-md-9 text-justify">
    <!--    <h4> Chi tiết tin</h4>-->
    <h4><?php echo $this->data['news_title']; ?></h4>
    <p><?= $this->data['news_content']; ?></p>


</div>






