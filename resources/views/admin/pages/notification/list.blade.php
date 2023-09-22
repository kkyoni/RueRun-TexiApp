@extends('admin.layouts.app')
@section('title')
    Mail Box
@endsection
@section('mainContent')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Mail Box Management</h2>
        </div>
    </div>

    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="mail-box-header">
                    <form method="get" action="index.html" class="float-right mail-search">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm search_email" name="search" placeholder="Search email">
{{--                            <div class="input-group-btn">--}}
{{--                                <button type="submit" class="btn btn-sm btn-primary">Search</button>--}}
{{--                            </div>--}}
                        </div>
                    </form>
                    <h2>Inbox ({{$notifications_count}})</h2>
                    <div class="mail-tools tooltip-demo m-t-md">
                        <div class="btn-group float-right">
                            <button class="btn btn-white btn-sm"><i class="fa fa-arrow-left"></i></button>
                            <button class="btn btn-white btn-sm"><i class="fa fa-arrow-right"></i></button>
                        </div>
                        <button class="btn btn-white btn-sm" id="detailed" data-toggle="tooltip" data-placement="left" title="Refresh inbox"><i class="fa fa-refresh"></i> Refresh</button>
                        <a href="{{ route('admin.setreadall') }}"><button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Mark as read"><i class="fa fa-eye"></i> </button></a>
                        <button class="btn btn-white btn-sm hidden" data-toggle="tooltip" data-placement="top" title="Move to trash"><i class="fa fa-trash-o"></i> </button>
{{--                        <div class="check-mail float-left"><input type="checkbox" class="i-checks selectall" id="selectall"></div>--}}
                    </div>
                </div>
                <div class="mail-box" id="main">
                    <table class="table table-hover table-mail email_table">
                        <tbody>
                        @foreach($notifications as $list)
                            <tr class="@if($list->is_read_user == 'unread')unread @else read @endif">
                                <td class="check-mail hidden">
                                    <input type="checkbox" class="i-checks emailstatus">
                                </td>
                                <td class="mail-ontact">
                                    <a href="{{ url('admin/mail_detail', $list->id) }}">

                                        {{$list->user->first_name}} {{$list->user->last_name}}</a></td>
                                <td class="mail-subject"><a href="">{{$list->title}}</a></td>
                                <td class="text-right mail-date">{{$list->created_at->diffForHumans()}}</td>
                            </tr>
                        @endforeach
                        <!-- <tr class="read">
									<td class="check-mail">
										<input type="checkbox" class="i-checks" checked>
									</td>
									<td class="mail-ontact"><a href="mail_detail.html">Jack Nowak</a></td>
									<td class="mail-subject"><a href="mail_detail.html">Aldus PageMaker including versions of Lorem Ipsum.</a></td>
									<td class=""></td>
									<td class="text-right mail-date">8.22 PM</td>
								</tr> -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('styles')
    <style>
        /*.mail-box{border:none;}*/
        .mail-box-header{border:none;}
    </style>
@endsection
@section('scripts')
    <script>
        $(document).ready(function(){
            $("#detailed").click(function(){
                location.reload(true);
            });

            $("#selectall").click(function (e) {

                if($(this).is(":checked")){
                    $(".emailstatus").prop("checked", false);
                    $(".emailstatus").prop("checked", true);
                }else{
                    $(".emailstatus").prop("checked", false);
                }
            });


            $(".search_email").on("keyup keydown", function() {
                var value = $(this).val().toLowerCase();
                $(".email_table tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endsection