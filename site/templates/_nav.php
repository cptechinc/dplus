<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <a href="#" class="nav-toggle pr-1 pl-2" onclick="toggle_nav()">
            <i class="fa fa-bars fa-1-2x" aria-hidden="true"></i>
        </a>
        <a href="<?= $pages->get('/')->url; ?>" class=""  aria-label="homepage link">
            <img src="<?= $siteconfig->child('name=customer')->logo_large->height(30)->url; ?>" alt="">
        </a>
        <a class="font-weight-bold" href="<?= $pages->get('/')->url; ?>" aria-label="homepage link">
            <img src="<?= $appconfig->logo_small->url; ?>" width="30" height="30" alt="">
            DistributionPlus
        </a>

    </div>
</nav>
<?php include('./_nav-yt.php'); ?>
