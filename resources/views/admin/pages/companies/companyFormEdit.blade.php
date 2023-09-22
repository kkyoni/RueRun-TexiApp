<div class="form-group  row {{ $errors->has('avatar') ? 'has-error' : '' }}">
  <div id="imagePreview" class="profile-image">
    @if(!empty($company_detail->driverDetail->avatar))
    <img src="{!! @$company_detail->avatar !== '' ? url("storage/avatar/".@$company_detail->driverDetail->avatar) : url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle">
    @else
    <img src="{!! url('storage/avatar/default.png') !!}" alt="user-img" class="img-circle" name="" accept="image/*">
    @endif
  </div>
  {!! Form::file('avatar',['id' => 'hidden','accept'=>"image/*",'class'=>'user_profile_pic']) !!}
</div>
<span >
  <center>  <font color="red"> {{ $errors->has('avatar') ? "".$errors->first('avatar')."" : '' }} </font> </center>
</span>
<br>

<div class="form-group  row {{ $errors->has('company_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Company Name</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('company_name',$company_detail->driverDetail->company_name,[
    'class' => 'form-control',
    'id'  => 'company_name',
    'maxlength' => '50'
    ]) !!}
    <span class="help-block">
      <font color="red"> {{ $errors->has('company_name') ? "".$errors->first('company_name')."" : '' }} </font>
    </span>
  </div>
</div>

<div class="form-group  row {{ $errors->has('recipient_name') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Applicant Name</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('recipient_name',null,[
    'class' => 'form-control',
    'id'  => 'recipient_name',
    'maxlength' => '50'
    ]) !!}
    <span class="help-block">
      <font color="red"> {{ $errors->has('recipient_name') ? "".$errors->first('recipient_name')."" : '' }} </font>
    </span>
  </div>
</div>

<div class="form-group  row {{ $errors->has('job_title') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Job Title</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('job_title',null,[
    'class' => 'form-control',
    'id'  => 'job_title',
    'maxlength' => '50'
    ]) !!}
    <span class="help-block">
      <font color="red"> {{ $errors->has('job_title') ? "".$errors->first('job_title')."" : '' }} </font>
    </span>
  </div>
</div>

<div class="form-group  row {{ $errors->has('job_title') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Company Size</strong> <span class="text-danger">*</span></label>

    <div class="col-sm-6">{!! Form::text('company_size',null,[
    'class' => 'form-control',
    'id'  => 'company_size','maxlength'=>'4'
    ]) !!}
        <span class="help-block">
      <font color="red"> {{ $errors->has('company_size') ? "".$errors->first('company_size')."" : '' }} </font>
    </span>
    </div>

</div>

<div class="form-group  row {{ $errors->has('website') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Company Website</strong> <span class="text-danger">*</span></label>
  <div class="col-sm-6">{!! Form::text('website',null,[
    'class' => 'form-control',
    'id'  => 'website',
    'maxlength' => '70'
    ]) !!}
    <span class="help-block">
      <font color="red"> {{ $errors->has('website') ? "".$errors->first('website')."" : '' }} </font>
    </span>
  </div>
</div>

@section('styles')
<style type="text/css">
  #imagePreview {
    width: 135px;
    height: 100%;
    text-align: center;
    margin: 0 auto;
    position: relative;
  }
  #hidden{
    display: none !important;
  }
  #imagePreview img {
    height: 150px;
    width: 150px;
    border: 3px solid rgba(0,0,0,0.4);
    padding: 3px;
  }
  #imagePreview i{
    position: absolute;
    right: 0px;
    background: rgba(0,0,0,0.5);
    padding: 5px;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    color: #fff;
    font-size: 18px;
  }
</style>
@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
            var max_fields = 15; //maximum input boxes allowed
            var wrapper = $(".input_fields_wrap"); //Fields wrapper
            var add_button = $(".add_field_button"); //Add button ID

            var x = 1; //initlal text box count
            $(add_button).click(function(e){ //on add input button click
                //alert('asdas');
                e.preventDefault();
                if(x < max_fields){ //max input box allowed
                    x++; //text box increment


                    $(wrapper).append('<div class ="remove_test"><div class="hr-line-dashed"></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Number</strong></label><div class="col-sm-6"><input type ="text" name="card_number[]" class="form-control credit-card" id="credit-card" maxlength="17" onkeypress="return isNumberKey(event)" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Holder Name</strong></label><div class="col-sm-6"><input type ="text" name="card_holder_name[]" class="form-control" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Name</strong></label><div class="col-sm-6"><input type ="text" name="card_name" class="form-control" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Expiry Month</strong></label><div class="col-sm-6"><input type ="number" name="card_expiry_month[]" class="form-control w-45 date_abc date" id="date" data-provide="datepicker"></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Card Expiry Year</strong></label><div class="col-sm-6"><input type ="number" name="card_expiry_year[]" class="form-control year" id="year" data-provide="datepicker"></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>CVV</strong></label><div class="col-sm-6"><input type ="text" name="cvv" class="form-control" id="cvv" ></div></div><div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Billing Address</strong></label><div class="col-sm-6"><input type ="text" name="billing_address[]" class="form-control "></div></div> <div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>Bank Name</strong></label><div class="col-sm-6"><input type ="text" name="bank_name[]" class="form-control" ></div></div> <div style="cursor:pointer;background-color:red;" class="remove_field btn btn-info">Remove</div></div>');//add input box
                    var date = new Date();
                    date.setDate(date.getDate());

                    $('.date').datepicker({
                      startDate: date,
                      format: "mm",
                      viewMode: "months",
                      minViewMode: "months"
                    });


                    var datee = new Date();
                    date.setDate(date.getDate());

                    $('.year').datepicker({
                      startDate: datee,
                      format: "yyyy",
                      viewMode: "years",
                      minViewMode: "years"
                    });


                    function isNumberKey(evt)
                    {
                      var charCode = (evt.which) ? evt.which : event.keyCode
                      if (charCode > 31 && (charCode < 48 || charCode > 57))
                        return false;

                      return true;
                    }

                    $('.credit-card').on('keypress change blur', function () {
                      $(this).val(function (index, value) {

                        return value.replace(/[^a-z0-9]+/gi, '').replace(/(.{4})/g, '$1 ');
                      });
                    });

                    $('.credit-card').on('copy cut paste', function () {
                      setTimeout(function () {
                        $('.credit-card').trigger("change");
                      });
                    });
                  }
                });
            $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
              e.preventDefault(); $(this).parent('div').remove(); x--;
            })
          });


        $(wrapper).on("click",".remove_field_id", function(e){ //user click on remove text
            //alert('sasdsda');
            e.preventDefault(); $(this).parent('.remove_test').remove(); x--;
          })
        </script>
        <script type="text/javascript">

          function isNumberKey(evt)
          {
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode > 31 && (charCode < 48 || charCode > 57))
              return false;

            return true;
          }

          $('.credit-card').on('keypress change blur', function () {
            $(this).val(function (index, value) {

              return value.replace(/[^a-z0-9]+/gi, '').replace(/(.{4})/g, '$1 ');
            });
          });

          $('.credit-card').on('copy cut paste', function () {
            setTimeout(function () {
              $('.credit-card').trigger("change");
            });
          });



          function readURL(input) {
            if (input.files && input.files[0]) {
              var reader = new FileReader();
              reader.onload = function(e) {
                $('#imagePreview img').attr('src', e.target.result);
              }
              reader.readAsDataURL(input.files[0]);
            }
          }
          $('#imagePreview img').on('click',function(){
            $('input[type="file"]').trigger('click');
            $('input[type="file"]').change(function() {
              readURL(this);
            });
          });


          var date = new Date();
          date.setDate(date.getDate());

          $('.date').datepicker({
            startDate: date,
            format: "mm",
            viewMode: "months",
            minViewMode: "months"
          });

          var datee = new Date();
          date.setDate(date.getDate());

          $('.year').datepicker({
            startDate: datee,
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years"
          });

          var datee = new Date();
          date.setDate(date.getDate());

          $('.manufacturing_year').datepicker({
            endDate: datee,
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years"
          });
        </script>

        <!-- iCheck -->
        <link href="{{ asset('assets/admin/js/plugins/iCheck/icheck.min.js')}}" rel="stylesheet">

        <script>
          $(document).ready(function () {
            $('.i-checks').iCheck({
              checkboxClass: 'icheckbox_square-green',
              radioClass: 'iradio_square-green',
            });

            // get cities as per state
            $(document).on("change","#states",function(e){
              var row = $(this);
              var id = $(this).val();
              if (id) {
                $.ajax({
                  url:"{{ route('admin.subadmin.getcities') }}",
                  type: 'post',
                  data: {"_method": 'post',
                  'id':id,
                  "_token": "{{ csrf_token() }}"
                },
                success:function(result){
                  var html='';

                  $("#city").html('');
                  html = '<option value>Please Select City</option>';
                  $.each(result.all_cities, function (key, val) {
                    html += '<option value="'+val.id+'">'+val.city+'</option>';
                  });
                  $("#city").html(html);
                },
                error:function(){
                  swal("Error!", 'Error in updated Record', "error");
                }
              });
              } else {
                swal("Cancelled", "Your Status is safe :)", "error");
              }
            });
          });

          $(".user_profile_pic").change(function() {
            var val = $(this).val();
            switch(val.substring(val.lastIndexOf('.') + 1).toLowerCase()){
              case 'gif': case 'jpg': case 'png': case 'jpeg':
                    //alert("an image");
                    break;
                    default:
                    $(this).val('');
                    // error message here
                    alert("not an image");
                    break;
                  }
                });

          $('#company_name, #recipient_name, #job_title, #country, #state_id, #city_id, #color').on('keyup onmouseout keydown keypress blur change', function (event) {
            var regex = new RegExp("^[a-zA-Z ._\\b\\t]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)) {
              event.preventDefault();
              return false;
            }
          });

          $('#company_size, #mileage, #year').on('keyup onmouseout keydown keypress blur change', function (event) {
            var regex = new RegExp("^[0-9 ._\\b\\t]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)) {
              event.preventDefault();
              return false;
            }


            $('#contact_number').on('keyup onmouseout keydown keypress blur change', function (event) {
            var regex = new RegExp("^[0-9 ._\\b\\t]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)) {
              event.preventDefault();
              return false;
            }

          function passwordStrength(password,ref) {

            var desc = [{'width':'0px'}, {'width':'20%'}, {'width':'40%'}, {'width':'60%'}, {'width':'80%'}, {'width':'100%'}];
            var descClass = ['', 'progress-bar-danger', 'progress-bar-danger', 'progress-bar-warning', 'progress-bar-success', 'progress-bar-success'];
            var score = 0;
            //if password bigger than 6 give 1 point
            if (password.length >= 8) score++;
            //if password has both lower and uppercase characters give 1 point
            if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/))) score++;
            //if password has at least one number give 1 point
            if (password.match(/d+/)) score++;
            //if password has at least one special caracther give 1 point
            if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) ) score++;
            //if password bigger than 12 give another 1 point
            if (password.length >= 8) score++;
            // display indicator

            if(score<4){
              ref.next('.help-block').find('font').html('Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters');
            }else{
              ref.next('.help-block').find('font').html('');
            }
            //$("#jak_pstrength").removeClass(descClass[score-1]).addClass(descClass[score]).css(desc[score]);
          }
          jQuery("#password").keyup(function() {
            passwordStrength(jQuery(this).val(),$(this));
          });
        </script>
        <script src="{{ asset('assets/admin/js//select2.full.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/admin/js/tagging.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('assets/admin/js/prism.min.js') }}" type="text/javascript"></script>
        <script type="text/javascript">
          $('#ride_type').select2({
            closeOnSelect: false
          });
        </script>
        @endsection
