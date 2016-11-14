function loadManifest() {
    jQuery.getJSON('manifest.webapp').done(function (data) {
        sessionStorage.setItem("serverUrl", data.activities.openmrs.href);
    }).fail(function (jqxhr, textStatus, error) {
        var err = textStatus + ", " + error;
        console.log("reading manifest file request Failed: " + err);
    });
}

$(function(){
  var getUrl = 'http://localhost:8080/openmrs/ws/rest/v1/session';
  $.ajax({
    url:getUrl,
    dataType:'json',
    success:function(data){
      console.log( sessionStorage.getItem('serverUrl'));
      console.log('session data:');
      console.log(data);
      $('p').append(data.user.person.display);
    }
  });

    var userUrl = 'http://localhost:8080/openmrs/ws/rest/v1/concept?limit=30';
    $.ajax({
    url:userUrl,
    dataType:'json',
    success:function(data){
      console.log('concpet data:');
      console.log(data);
      // $('p').append(data.user.person.display);
     }
  });

    var patientUrl = 'http://localhost:8080/openmrs/ws/rest/v1/patient';
    $.ajax({
    url:patientUrl,
    dataType:'json',
    success:function(data){
      console.log('patient data:');
      console.log(data);
      // $('p').append(data.user.person.display);
    }
  });

  var token = "admin:Admin123";
  var alterToken = "QWxhZGRpbjpvcGVuIHNlc2FtZQ==";
  var patientUrl = 'http://localhost:8080/openmrs/ws/rest/v1/patient';
  $.ajax({
  url:patientUrl,
  type:'get',
<<<<<<< Updated upstream
  beforeSend: function(xhr) {
         xhr.setRequestHeader("Authorization", "Basic " + alterToken)
=======
  beforeSend: function(xhr) {
         xhr.setRequestHeader("Authorization", "Basic " + alterToken)
>>>>>>> Stashed changes
     },
  success:function(data){
    console.log('v1 patient:');
    console.log(data);
    // $('p').append(data.user.person.display);
  }
});

});
