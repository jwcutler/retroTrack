<div id="login_form">
    <div class="form_header" style="padding-bottom: 20px;text-align: center;">retroTrack Administrator Login</div>
    
    <form action="/admin/login" method="POST">
        <div class="login_form_label">Username</div>
        <input type="text" name="data[Admin][username]" maxlength="50" style="width: 100%;" />
        <div class="login_form_label">Password</div>
        <input type="text" name="data[Admin][password]" style="width: 100%;" />
        
        <br /><br />
        <center>
            <input type="submit" value="Login To Administrator Panel" class="btn btn-primary btn-large">
        </center>
    </form>
</div>

