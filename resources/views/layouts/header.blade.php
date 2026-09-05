<?php $setting = App\Helpers\Utility::setting();?>
<header>
    <section id="banner">
        <img src="{{Storage::disk('admin')->url($setting->logo)}}"/>
    </section>
    <div id="menu">
        <ul class="mainmenu">
            <li class="homeitem">
                <a href="/" style="cursor: pointer;">
                    <i class="fas fa-home" style="font-size: 12px; color: rgb(255, 255, 255); cursor: pointer;"></i>
                    Home
                </a>
            </li>
            <?php $menus = \App\Helpers\Utility::getMenus();
            foreach ($menus as $row){
            ?>
            <li><a href="{{$row->link}}" style="cursor: pointer;">{{$row->name}}</a>
            <?php if(count($row->subs) > 0)  {?>
                <ul class="sub-menu">
                    <?php foreach ($row->subs as $row1) {?>
                    <li><a class="ared" href="{{$row1->link}}"><b> {{$row1->name}} </b></a></li>
                    <?php } ?>
                </ul>
            <?php }?>
            </li>
            <?php }?>
        </ul>
    </div>
    <div class="middledite">
        <ol class="breadcrumb" style="margin-bottom: 0px;margin-top: auto;">
            <?php $pages = \App\Helpers\Utility::getPages();
            foreach ($pages as $row){
                ?>
            <li><a href="/du-doan-xo-so-{{$row->slug}}" class="active" style="color:#0066FF;">{{$row->name}}</a> <img src="/assets/images/hot2.gif" alt="{{$row->name}}"></li>
           <?php } ?>
        </ol>
    </div>

</header>
