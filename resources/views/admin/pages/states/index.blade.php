@extends('admin.layouts.app')
@section('title')
States
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>States</h2>
	</div>
	<div class="col-lg-2">
	</div>
</div>

<div class="wrapper wrapper-content">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox ">
				<div class="ibox-content">
					<div class="col-md-12 text-right">
						@php 
						$checkPermission = \App\Helpers\Helper::checkPermission(['states-create']);
						@endphp
						@if($checkPermission)
						<a class="btn btn-primary btn-sm pull-right mb-3 op-btn" data-toggle="modal" data-target="#exampleModalCenter" style="color: white;">
							<i class="icon-plus fa-fw"></i>
							Add State
						</a>
						@endif
						<div class="clearfix"></div>
					</div>
					<div class="col-md-12">
						<div class="table-responsive">
							{!! $html->table(['class' => 'table table-striped table-bordered dt-responsive'], true) !!}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Add State</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			{!!
			Form::open([
			'route'	=> ['admin.states.store'],
			'id'	=> 'userCreateForm',
			'files' => 'true'
			])
			!!}
			<div class="modal-body">
				<div class="form-group  row {{ $errors->has('state') ? 'has-error' : '' }}"><label class="col-sm-3 col-form-label"><strong>State</strong> <span class="text-danger">*</span></label>
					<div class="col-sm-9">{!! Form::text('state',null,[
						'class' => 'form-control',
						'id'	=> 'state','required',
						'maxlength' => '35'
						]) !!}
						<span class="help-block">
							<font color="red"> {{ $errors->has('state') ? "".$errors->first('state')."" : '' }} </font>
						</span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>




@endsection

@section('styles')

<style type="text/css">
	table.dataTable {
		clear: both;
		margin-top: 6px !important;
		margin-bottom: 6px !important;
		max-width: none !important;
		border-collapse: separate !important;
		width: 100% !important;
	}
	.op-btn{
		margin-right:22px;
	}
</style>

@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
{!! $html->scripts() !!}
<script type="text/javascript">
	$(document).on("click","a.deletestate",function(e){
		var row = $(this);
		var id = $(this).attr('data-id');
		swal({
			title: "Are you sure?",
			text: "You will not be able to recover this record",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#e69a2a",
			confirmButtonText: "Yes, delete it!",
			cancelButtonText: "No, cancel please!",
			closeOnConfirm: false,
			closeOnCancel: false
		}, function(isConfirm){
			if (isConfirm) {
				$.ajax({
					url:"{{route('admin.states.delete',[''])}}"+"/"+id,
					type: 'post',
					data: {"_token": "{{ csrf_token() }}"
				},
				success:function(msg){
					if(msg.status == 'success'){
						location.reload();
					}else{
						location.reload();
					}
				},
				error:function(){
					swal("Error!", 'Error in delete Record', "error");
				}
			});
				//swal("Deleted!", "Operator has been deleted.", "success");

			} else {
				swal("Cancelled", "Your state is safe :)", "error");
			}
		});
		return false;
	})

	$('#state, .state').on('keyup onmouseout keydown keypress blur change', function (event) {  console.log(event);
		var regex = new RegExp("^[a-zA-Z ._\\b\\t]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
			event.preventDefault();
			return false;
		}
	});
</script>
@endsection
