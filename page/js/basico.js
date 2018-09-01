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
  console.info($('#pago').serialize());
  $.ajax({
    method: "POST",
    type: 'POST',
    url: "/service/cargar",
    dataType: 'json',
    data: $('#pago').serialize(),
    success: function(response) {
      if(response.url){
        $(location).attr('href', response.url);
      } else {
        alert('Error del servidor de pagos');
      }
    }
  });
}

function procesar(){
  $.ajax({
    method: "POST",
    type: 'POST',
    url: "/service/procesar",
    dataType: 'json',
    data: {},
    success: function(response) {
      if(response.url){
        $(location).attr('href', response.url);
      } else {
        alert('Error del servidor de pagos');
      }
    }
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
  $('#btn-procesar').click(function(){
    procesar();
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
