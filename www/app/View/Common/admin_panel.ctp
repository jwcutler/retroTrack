<style type="text/css">
.nav-list a {
    text-decoration: none;
}
.nav-list > li > a {
    color: #0088CC;
}
.nav-list > li > a:hover {
    color: #005580;
}
</style>
<div class="page_title" style="margin-bottom: 20px;"><?php echo Configure::read('Website.name'); ?> Administration Panel</div>
<div id="admin_left_column">
    <div id="admin_nav_menu">
        <ul class="nav nav-list">
            <li class="nav-header">
                <?php echo Configure::read('Website.name'); ?> Settings
            </li>
            <li <?php if($this->params['controller']=='panel'){echo "class=\"active\"";} ?>>
                <a href="<?php echo $this->Html->url(array('controller' => 'panel', 'action' => 'index', 'admin' => true)); ?>">Main Configuration</a>
            </li>
            <li <?php if($this->params['controller']=='satellite'){echo "class=\"active\"";} ?>>
                <a href="<?php echo $this->Html->url(array('controller' => 'satellite', 'action' => 'index', 'admin' => true)); ?>">Satellites</a>
            </li>
            <li <?php if($this->params['controller']=='group'){echo "class=\"active\"";} ?>>
                <a href="<?php echo $this->Html->url(array('controller' => 'group', 'action' => 'index', 'admin' => true)); ?>">Satellite Groups</a>
            </li>
            <li <?php if($this->params['controller']=='station'){echo "class=\"active\"";} ?>>
                <a href="<?php echo $this->Html->url(array('controller' => 'station', 'action' => 'index', 'admin' => true)); ?>">Ground Stations</a>
            </li>
            <li <?php if($this->params['controller']=='export'){echo "class=\"active\"";} ?>>
                <a href="<?php echo $this->Html->url(array('controller' => 'export', 'action' => 'index', 'admin' => true)); ?>">Static Version Exporter</a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="<?php echo $this->Html->url(array('controller' => 'panel', 'action' => 'logout', 'admin' => true)); ?>">Logout</a>
            </li>
        </ul>
    </div>
</div>
<div id="admin_right_column">
    <?php echo $this->fetch('panel_content'); ?>
</div>

