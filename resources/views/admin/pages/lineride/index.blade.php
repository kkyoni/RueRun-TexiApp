@extends('admin.layouts.app')
@section('title')
Shuttle Ride Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Shuttle Ride Management</h2>
	</div>
</div>
<div class="wrapper wrapper-content">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox">
				<div class="ibox-content">
					<div class="col-md-12 text-right">
						<div class="clearfix"></div>
					</div>
					<div class="col-md-12">
						<div class="table-responsive">
							<select class="col-md-2 mr-3 pull-right form-control country_id" onchange="window.location.href=this.options[this.selectedIndex].value;">
							<option value="{{ route('admin.tripindex') }}">Booking</option>
							<option value="{{ route('admin.parcels.index') }}">Parcel Booking</option>
							<option selected="selected" value="{{ route('admin.lineride.index') }}">Shuttle Ride Booking</option>
						</select>
							{!! $html->table(['class' => 'table table-striped table-bordered dt-responsive'], true) !!}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('styles')
<style type="text/css">
	table.dataTable {clear: both;margin-top: 6px !important;margin-bottom: 6px !important;max-width: none !important;border-collapse: separate !important;width: 100% !important;}
	.op-btn{margin-right:22px;}
	.modal_css{max-width: 700px;margin: 1.75rem auto;}
	@media screen and (min-width: 768px) {
		.modal-dialog {width: 700px;}
		.modal-sm {width: 350px;}
	}
	@media screen and (min-width: 992px) {
		.modal-lg {width: 950px;}
	}
</style>
@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
{!! $html->scripts() !!}
@endsection