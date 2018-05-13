<input type="hidden" id="requests-user" value="0" />
<input type="hidden" id="requests-query" value="0" />

<div class="header container" style="text-align: right;">
    <div class="row">
        <a id="search-button" class="button button-primary" href="/" style="display:none">NUEVA BUSQUEDA</a>
        <a class="button button" href="/?edit=true">EDITAR API Keys</a>
    </div>
</div>

<div id="search-box" class="section hero" style="padding: 5rem 0;">
    <div class="container">
        <div class="row">
            <div class="one-half column">
                <h4 class="hero-heading">Ingresa un usuario de Twitter a ser analizado</h4>
            </div>
        </div>
        <form id="formulario" action="" method="POST" accept-charset="UTF-8">
            <div class="ten columns">
                <input class="u-full-width" id="text_u" type="text" name="text" value="<?php echo isset($user)?$user:'';?>" placeholder="Usuario de Twitter sin @"/>
                <input class="u-full-width" id="text_w" type="hidden" value="" />
            </div>

            <div class="two columns">
                <input id="submit" type="submit" class="button-primary" value="analizar">
            </div>
        </form>
    </div>
</div>

<div class="section values" id="stats" style="display: none;padding: 5rem 0;">
    <div class="container">
        <div class="row">
            <div class="three columns value">
            <h2 class="value-multiplier"><span id="twits-user">0</span></h2>
            <h5 class="value-heading">Cantidad de twits analizados</h5>
            <p class="value-description"></p>
            </div>

            <div class="three columns column value">
            <h2 class="value-multiplier"><span id="twits-user-requests">0</span></h2>
            <h5 class="value-heading">Búsquedas por usuario</h5>
            <p class="value-description"></p>
            </div>

            <div class="three columns column value">
            <h2 class="value-multiplier"><span id="twits-query-requests">0</span></h2>
            <h5 class="value-heading">Búsquedas por palabra clave</h5>
            <p class="value-description"></p>
            </div>

            <div class="three columns column value">
            <h2 class="value-multiplier"><span id="twits-mentions">0</span></h2>
            <h5 class="value-heading">Usuarios identificados</h5>
            <p class="value-description"></p>
            </div>
        </div>
    </div>
</div>

<div class="section hero" style="padding: 5rem 0;">
    <div class="container">
        <div class="row">
            <div id="tags" class="demo jqcloud" style="margin: 0 auto;"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var words = '';

    function updateTags() {
      var q = $('#text_u').val();

      $.post('', {tags: 1, q: q})
        .success(function(data) {
          words = JSON.parse(data);

          $('#twits-mentions')
            .prop('number', $('#twits-mentions').text())
              .animateNumber(
                {number: words.length},
                1000
              );
      });

      setTimeout(updateTags, 5000);
    }

    $('#submit').prop('disabled', true);
    $('#submit').hide();

    $("document").ready(function(){
        $( "#text_u" ).keyup(function() {
            if($( "#text_u" ).val().length > 0){
                $('#submit').prop('disabled', false);
                $('#submit').show('fast');
            }
            else{
                $('#submit').prop('disabled', true);
                $('#submit').hide('fast');
            }
        });

        $("#formulario").submit(function( event ) {
            event.preventDefault();

            if($('#text_u').val()  == ''){
                return false;
            }

            setTimeout(updateTags, 5000);

            $('#search-box').hide();
            $('#search-button').show();
            $('#stats').show("slow");

            /* QUERY TWITS*/
            function batchPromiseRecursive(max_id) {
                //query string a buscar
                var q = $('#text_u').val();

                if (q.length == 0) {
                    return $.Deferred().resolve().promise();
                }

                return $.post('', {q: q, max_id: max_id})
                        .done(function(serverData) {
                          max_id = JSON.parse(serverData).max_id;
                          count  = JSON.parse(serverData).count;


                          $('#twits-user')
                            .prop('number', $('#twits-user').text())
                              .animateNumber(
                                {number: count},
                                1000
                              );


                          $('#requests-query').val(parseInt($('#requests-query').val()) + 1);
                          $('#twits-query-requests').text($('#requests-query').val());
                        })
                        .fail(function(e) {
                          // do something here.
                        })
                        .then(function() {
                            setTimeout(
                              function()
                              {
                                //do something special
                              }, 5000
                            );

                            return batchPromiseRecursive(max_id);
                        });
            }

            batchPromiseRecursive(0).then(function() {
              // something to do when it's all over.
            });


            /* USER TWITS */
            function userPromiseRecursive(user, max_id) {
                //query string a buscar
                var q = $('#text_u').val();

                if (q.length == 0) {
                    return $.Deferred().resolve().promise();
                }

                return $.post('', {query: q, user: user, max_id: max_id})
                        .done(function(serverData) {
                          max_id = JSON.parse(serverData).max_id;
                          //count  = JSON.parse(serverData).count;
                          user   = JSON.parse(serverData).user;

                          console.log(user + ' - ' + max_id);

                          //$('#twits-query').text(count);

                          $('#requests-user').val(parseInt($('#requests-user').val()) + 1);
                          $('#twits-user-requests').text($('#requests-user').val());

                          /*
                          $('#twits-user')
                            .prop('number', $('#twits-user').text())
                              .animateNumber(
                                {number: count},
                                1000
                              );
                          */

                          $('#tags').jQCloud('update', words);
                        })
                        .fail(function(e) {
                          // do something here.
                        })
                        .then(function() {
                            setTimeout(
                              function()
                              {
                                //do something special
                              }, 5000
                            );

                            return userPromiseRecursive(user, max_id);
                        });
            }

            userPromiseRecursive($('#text_u').val(), 0).then(function() {
              // something to do when it's all over.
            });
        });


        $('#tags').jQCloud(words,  {
          width: 800,
          height: 350
        });
    });
</script>
