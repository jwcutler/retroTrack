<div class="users form">
<?php echo $this->Form->create('Admin'); ?>
    <fieldset>
        <legend><?php echo __('Add Admin'); ?></legend>
    <?php
        echo $this->Form->input('username');
        echo $this->Form->input('password');
    ?>
    </fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
