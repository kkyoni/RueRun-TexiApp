
<div class="form-group  row {{ $errors->has('username') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Users</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-9">
	 {!! Form::select('user_email[]', $users , null, [
		'class' => 'form-control userlist','id'=>'user_email','multiple' => 'multiple',
		]  ) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('user_email') ? "".$errors->first('user_email')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group  row {{ $errors->has('drivers') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Drivers</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-9">
	 {!! Form::select('driver_email[]', $drivers , null, [
		'class' => 'form-control userlist','id'=>'driver_email','multiple' => 'multiple',
		]  ) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('driver_email') ? "".$errors->first('driver_email')."" : '' }} </font>
		</span>
	</div>
</div>


<div class="form-group  row {{ $errors->has('companies') ? 'has-error' : '' }}">
    <label class="col-sm-3 col-form-label"><strong>Companies</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-9">
	 {!! Form::select('company_email[]', $companies , null, [
		'class' => 'form-control userlist','id'=>'company_email','multiple' => 'multiple'
		]  ) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('company_email') ? "".$errors->first('company_email')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group  row {{ $errors->has('title') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Title</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-9">
			{!! Form::text('title',null,[
			     'class'         => 'form-control',
			     'id'            => 'title',
			     'placeholder'   => 'Enter title',
			]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('title') ? "".$errors->first('title')."" : '' }} </font>
		</span>
	</div>
</div>


<div class="form-group  row {{ $errors->has('description') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Description</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-9">{!! Form::textarea('description',null,[
		'class' => 'form-control',
		'id'	=> 'description'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('description') ? "".$errors->first('description')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="hr-line-dashed"></div>

<div class="col-sm-6">
	<div class="form-group row">
		<div class="col-sm-8 col-sm-offset-8">
			<!-- <a href="{{route('admin.notification.index')}}"><button class="btn btn-danger btn-sm" type="button">Cancel</button></a> -->
			<button class="btn btn-primary btn-sm" type="submit">Send Mail</button>
		</div>
	</div>
</div>

@section('styles')
<style type="text/css">
	.help-block {
		display: inline-block;
		margin-top: 5px;
		margin-bottom: 0px;
		margin-left: 5px;
	}
	.form-group {
		margin-bottom: 10px;
	}
	.form-control {
		font-size: 14px;
		font-weight: 500;
	}

	#hidden{
		display: none !important;
	}

</style>

<link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/form-extended.css') }}">
<style>
    .tagging {
        border: 1px solid #CCCCCC;
        cursor: text;
        font-size: 1em;
        height: auto;
        padding: 0.75rem 1rem;
        line-height: 1.25;
        display: block;
    }
    .tagging .tag {
        background: none repeat scroll 0 0 #EE7407;
        border-radius: 2px;
        color: white;
        cursor: default;
        display: inline-block;
        position: relative;
        white-space: nowrap;
        padding: 5px 25px 6px 0px;
        margin: 3px;
    }
</style>
@endsection
@section('scripts')

<script src="{{ asset('assets/admin/js//select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/admin/js/tagging.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/admin/js/prism.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    $('#user_email').select2({
        closeOnSelect: false
    });
    $('#driver_email').select2({
        closeOnSelect: false
    });
    $('#company_email').select2({
        closeOnSelect: false
    });
</script>

<script>
	$(document).ready(function () {
		$('.i-checks').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
		});
	});


</script>
<script>
$('#langOpt3, #companies, #drivers').multiselect({
    columns: 1,
    placeholder: 'Select Username',
    search: true,
    selectAll: true
});
</script>

<script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>
<script>
    var editor = CKEDITOR.replace( 'description', {
        language: 'en',
        toolbar :
       [
        { name: 'document', items : [ 'NewPage','Preview' ] },
        { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
        { name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','Scayt' ] },
        { name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'
                 ,'Iframe' ] },
                '/',
        { name: 'styles', items : [ 'Styles','Format' ] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Strike','-','RemoveFormat' ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote' ] },
        { name: 'links', items : [ 'Link','Unlink','Anchor' ] },
        { name: 'tools', items : [ 'Maximize','-','About' ] }
    ],
        extraPlugins: 'notification'
    });

    editor.on( 'required', function( evt ) {
        editor.showNotification( 'This field is required.', 'warning' );
        evt.cancel();
    } );

</script>
@endsection


