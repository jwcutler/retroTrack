<script type="text/javascript">
$(document).ready(function(){
    // Handle form submission
    $("#generate_password_hash").submit(function(event){
        $("#post_response").hide();
        event.preventDefault();
        password = $("#password").val();
        post_path = $(this).attr('action');
        
        $.post(post_path, {password: password}, function(data) {
            $("#post_response").html(data);
            $("#post_response").show();
        });
    });
    
    // Form validation
    var container = $("div.form_errors");
    $("#generate_password_hash").validate({
        errorContainer: container,
        errorLabelContainer: $("ul", container),
        errorClass: "form_error",
        wrapper: 'li',
        rules: {
            password: {
                required: true
            },
            password2: {
                required: true,
                equalTo: "#password"
            }
        },
        messages: {
            password: {
                required: "Please enter your password."
            },
            password2: {
                required: "Please confirm your password.",
                equalTo: "Your passwords do not match."
            }
        }
    });
});
</script>
<h3>Create A Password Hash</h3>
This page is used to create a new password hash for use with the retroTracker administration panel.
<div class="form_errors">
    <ul>
    </ul>
</div>
<div style="display: none; color: green; margin: 10px 0px 10px 0px;" id="post_response"></div>
<div style="width: 70%; margin-top: 10px;">
    <form action="/admin/panel/generatehash" method="POST" class="form-horizontal" id="generate_password_hash">
        <div class="control-group">
            <label class="control-label" for="password">Desired Password</label>
            <div class="controls">
                <input type="password" name="password" id="password" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="password2">Password (again)</label>
            <div class="controls">
                <input type="password" name="password2" id="password2" />
            </div>
        </div>
        <button type="submit" class="btn btn-success">Generate Password Hash</button>
    </form>
</div>
