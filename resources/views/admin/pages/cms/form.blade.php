
<div class="form-group  row {{ $errors->has('page_title') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Title</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-9">{!! Form::text('page_title',null,[
		'class' => 'form-control',
		'id'	=> 'page_title',
		'maxlength' => '30'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('page_title') ? "".$errors->first('page_title')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="form-group row {{ $errors->has('page_description') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>Page Content</strong> <span class="text-danger">*</span></label>
	<div class="col-sm-9">{!! Form::textarea('page_description',null,[
		'class' => 'form-control ',
		'id'	=> 'page_description'
		]) !!}
		<span class="help-block">
			<font color="red"> {{ $errors->has('page_description') ? "".$errors->first('page_description')."" : '' }} </font>
		</span>
	</div>
</div>

<div class="hr-line-dashed"></div>
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

<!-- iCheck -->
<link href="{{ asset('assets/admin/js/plugins/iCheck/icheck.min.js')}}" rel="stylesheet">

<script>
	$(document).ready(function () {
		$('.i-checks').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
		});

		$('#page_title').on('keyup onmouseout keydown keypress blur change', function (event) {
			var regex = new RegExp("^[a-zA-Z ._\\b]+$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}
		});
	});
</script>
<script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>
<script>
	var editor = CKEDITOR.replace( 'page_description', {
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

