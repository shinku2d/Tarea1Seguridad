function login(usuario, contrasena) {
  $.ajax({
    method: "POST",
    url: "/service/login",
    dataType: 'json',
    data: { 'usuario': usuario, 'contrasena': contrasena }
  }).done(function( html ) {

  });
}

function logout() {
  $.ajax({
    method: "POST",
    url: "/service/login",
    dataType: 'json',
    data: {}
  }).done(function( html ) {

  });
}

function pagar(){
  $.ajax({
    method: "POST",
    type: 'POST',
    url: "/service/pagar",
    dataType: 'json',
    data: {},
    success: function(response) {
      alert("Correcto");
      //window.location.href = response;
      $(location).attr('href', response.url);
    }
    /*
    error: function(response){
      alert("Error");
      console.info(response);
      //window.location.href = response;
    }
    */
  });
}

function setMonto(monto) {
  $('#monto').val(monto)
}

$(document).ready(function(){
  $('#btn-login').click(function(){
    login($('#usuario').val(), $('#contrasena').val());
  });
  $('#btn-logout').click(function(){
    logout();
  });
  $('#btn-pagar').click(function(){
    pagar();
  });
  $('.tipos input').click(function(){
    switch ($(this).val()) {
      case '500':
          setMonto(10000);
        break;
      case '1000':
          setMonto(18000);
        break;
      case '2000':
          setMonto(25000);
        break;
      default:

    }
  });
})
