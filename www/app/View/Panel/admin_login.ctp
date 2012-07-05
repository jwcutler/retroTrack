<div id="login_form">
    <div class="form_header" style="padding-bottom: 20px;text-align: center;"><?php echo Configure::read('Website.name'); ?> Administrator Login</div>
    
    <form action="/admin/panel/login" method="POST">
        <div class="login_form_label">Username</div>
        <input type="text" name="username" maxlength="50" style="width: 100%;" />
        <div class="login_form_label">Password</div>
        <input type="password" name="password" style="width: 100%;" />
        
        <br /><br />
        <center>
            <input type="submit" value="Login To Administrator Panel" class="btn btn-primary btn-large">
        </center>
    </form>
</div>

