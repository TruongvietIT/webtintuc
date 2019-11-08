<body>
<?php var_dump($this->categories); ?>
<div class="container-fluid">
    <nav class="navbar navbar-expand-md bg-dark navbar-dark row">
        <!-- Brand -->
        <a class="navbar-brand" href="#">Web tin tức</a>

        <!-- Toggler/collapsibe Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <ul class="navbar-nav">
                <?php foreach ($this->categories as $category ): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#"><?= $category['category_name'] ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</div>
<!--end nav-->