<div class="container">
    <form id="formulario" action="" method="POST" accept-charset="UTF-8">
        <div class="twelve columns">
            <input class="u-full-width" id="oauth_access_token" type="text" name="oauth_access_token" value="<?php echo isset($user)?$user:'';?>" placeholder="Oauth Access Token"/>

            <input class="u-full-width" id="oauth_access_token_secret" type="text" name="oauth_access_token_secret" value="<?php echo isset($user)?$user:'';?>" placeholder="Oauth Access Token Secret"/>

            <input class="u-full-width" id="consumer_key" type="text" name="consumer_key" value="<?php echo isset($user)?$user:'';?>" placeholder="Consumer Key"/>

            <input class="u-full-width" id="consumer_secret" type="text" name="consumer_secret" value="<?php echo isset($user)?$user:'';?>" placeholder="Consumer Secret"/>

            <a id="test" class="button">test</a>

            <input id="submit" type="submit" class="button-primary" value="guardar">

            <span id="msg"></span>
        </div>
    </form>
</div>

<script type="text/javascript">
$('#submit').prop('disabled', true);
$('#submit').hide();

$("document").ready(function(){
    $( 'input[type="text"]' ).keyup(function() {
        var isValid = true;

        $( 'input[type="text"]' ).each(function() {
           var element = $(this);
           if (element.val() == "") {
               isValid = false;
           }
        });

        if(isValid){
            $('#submit').prop('disabled', false);
            $('#submit').show('fast');
        }
        else{
            $('#submit').prop('disabled', true);
            $('#submit').hide('fast');
        }
    });

    $('#test').click(function(){
        event.preventDefault();

        var oauth_access_token = $('#oauth_access_token').val();
        var oauth_access_token_secret = $('#oauth_access_token_secret').val();
        var consumer_key = $('#consumer_key').val();
        var consumer_secret = $('#consumer_secret').val();
        var account = $('#account').val();

        $.post('', {test: true, params: { oauth_access_token: oauth_access_token, oauth_access_token_secret: oauth_access_token_secret, consumer_key: consumer_key, consumer_secret: consumer_secret, account: account} } )
            .success(function(serverData) {
                if(serverData == 'true'){
                    $('#msg').html(' Datos correctos');
                    $('#msg').addClass('success');
                    $('#msg').removeClass('error');
                }

                if(serverData == 'error'){
                    $('#msg').html(' Error en los datos');
                    $('#msg').addClass('error');
                    $('#msg').removeClass('success');
                }
                //window.history.replaceState({}, document.title, "/");
                //location.reload();
            })
            .fail(function(e) {
                console.log('error');
            });
    });

    $("#formulario").submit(function( event ) {
        event.preventDefault();

        var oauth_access_token = $('#oauth_access_token').val();
        var oauth_access_token_secret = $('#oauth_access_token_secret').val();
        var consumer_key = $('#consumer_key').val();
        var consumer_secret = $('#consumer_secret').val();
        var account = $('#account').val();

        $.post('', {config: true, params: { oauth_access_token: oauth_access_token, oauth_access_token_secret: oauth_access_token_secret, consumer_key: consumer_key, consumer_secret: consumer_secret, account: account} } )
            .success(function(serverData) {
                window.history.replaceState({}, document.title, "/");
                location.reload();
            })
            .fail(function(e) {
                console.log('error');
            });
    });
});
</script>
