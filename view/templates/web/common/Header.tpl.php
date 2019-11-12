<body>
<?php
$route = Context::getInstance()->getRoute();
$domain = Context::getInstance()->getFront()->getRequest()->getDomain() . '/';
$pageType = Context::getInstance()->getCachedParam('pageType');
$pageType = !empty($pageType) ? $pageType : 1;
$isMobile = Context::getInstance()->getFront()->getRequest()->isMobile();
$showAdsense = Context::getInstance()->getCachedParam('showAdsense') == '0' ? false : true;
?>
<div class="container-fluid" style="margin-bottom: 20px">
    <nav class="navbar navbar-expand-md bg-dark navbar-dark row">
        <!-- Brand -->
        <a class="navbar-brand" href="<?php echo $domain; ?>">Trang chủ</a>

        <!-- Toggler/collapsibe Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <ul class="navbar-nav">
                <?php
                if (isset($this->categories) && !empty($this->categories)):
                    foreach ($this->categories as $category):
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $domain.$category['category_link'] ?>"><?= $category['category_name'] ?></a>
                        </li>
                    <?php endforeach;
                endif ?>
            </ul>
        </div>
    </nav>
</div>
<!--end nav-->