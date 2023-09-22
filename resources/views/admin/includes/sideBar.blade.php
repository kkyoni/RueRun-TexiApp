<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="{{asset(\Settings::get('application_logo'))}}"  height="70px" width="67px"/>
                </div>
                <div class="logo-element">
                    <a href="{{Route('admin.dashboard')}}">
                        <img alt="{{Settings::get('application_title')}}" class="rounded-circle" src="{{asset(\Settings::get('application_logo'))}}"  height="30px" width="30px"/>
                    </a>
                </div>
            </li>
            <li class="@if(Request::segment('2') == 'dashboard') active @endif">
                <a href="{{ route('admin.dashboard') }}" data-toggle="tooltip" title="Dashboard">
                    <i class="fa fa-home"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>

            @php
            $userPermission = \App\Helpers\Helper::checkPermission(['user-list','user-create','user-edit','user-delete']);
            
            $driverPermission = \App\Helpers\Helper::checkPermission(['driver-list','driver-create','driver-edit','driver-delete']);

            $companydriverPermission = \App\Helpers\Helper::checkPermission(['company-driver-list','company-driver-create','company-driver-edit','company-driver-delete']);
            @endphp
            @if($userPermission || $driverPermission || $companydriverPermission)
            <li class="@if(Request::segment('2') == 'admin') active @endif @if(Request::segment('2') == 'user') active @endif @if(Request::segment('2') == 'driver') active @endif @if(Request::segment('2') == 'company') active @endif @if(Request::segment('2') == 'subadmin') active @endif @if(Request::segment('2') == 'subadmin_permission') active @endif">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span class="nav-label">User Management</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @php
                    $userPermission = \App\Helpers\Helper::checkPermission(['user-list','user-create','user-edit','user-delete']);
                    @endphp
                    @if($userPermission)
                    <li class="@if(Request::segment('2') == 'user') active @endif">
                        <a href="{{ route('admin.index') }}" data-toggle="tooltip" title="User"> User </a>
                    </li>
                    @endif

                    @php
                    $driverPermission = \App\Helpers\Helper::checkPermission(['driver-list','driver-create','driver-edit','driver-delete']);
                    @endphp
                    @if($driverPermission)
                    <li class="@if(Request::segment('2') == 'driver') active @endif">
                        <a href="{{ route('admin.driver.index') }}" data-toggle="tooltip" title="Individual - Driver">Individual - Driver </a>
                    </li>
                    @endif

                    @php
                    $companydriverPermission = \App\Helpers\Helper::checkPermission(['company-driver-list','company-driver-create','company-driver-edit','company-driver-delete']);
                    @endphp
                    @if($companydriverPermission)
                    <li class="@if(Request::segment('2') == 'company') active @endif">
                        <a href="{{ route('admin.company.index') }}" data-toggle="tooltip" title="Company - Driver">Company - Driver </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif



            @php
            $vehiclePermission = \App\Helpers\Helper::checkPermission(['vehicle-list','vehicle-create','vehicle-edit','vehicle-delete']);

            $vehiclemodelPermission = \App\Helpers\Helper::checkPermission(['vehiclemodel-list','vehiclemodel-edit','vehiclemodel-create','vehiclemodel-delete']);

            $vehicletypePermission = \App\Helpers\Helper::checkPermission(['vehicletype-list','vehicletype-edit','vehicletype-create','vehicletype-delete']);

            @endphp

            @if($vehiclePermission || $vehicletypePermission  || $vehiclemodelPermission)
            <li class="@if(Request::segment('2') == 'vehiclebody') active @endif @if(Request::segment('2') == 'vehicletype')  active @endif @if(Request::segment('2') == 'vehiclecategories') active @endif">
                <a href="#">
                    <i class="fa fa-bus"></i>
                    <span class="nav-label">Vehicle Management</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @php
                    $vehiclemodelPermission = \App\Helpers\Helper::checkPermission(['vehiclemodel-list','vehiclemodel-edit','vehiclemodel-create','vehiclemodel-delete']);
                    @endphp
                    @if($vehiclemodelPermission)
                    <li class="@if(Request::segment('2') == 'vehiclebody') active @endif">
                        <a href="{{ route('admin.vehiclebody.index') }}" data-toggle="tooltip" title="Vehicle Model">Make</a>
                    </li>
                    @endif

                    @php
                    $vehicletypePermission = \App\Helpers\Helper::checkPermission(['vehicletype-list','vehicletype-edit','vehicletype-create','vehicletype-delete']);
                    @endphp
                    @if($vehicletypePermission)
                    <li class="@if(Request::segment('2') == 'vehicletype') active @endif">
                        <a href="{{ route('admin.vehicletype.index') }}" data-toggle="tooltip" title="Vehicle Type">Model</a>
                    </li>
                    @endif

                    @php
                    $vehiclePermission = \App\Helpers\Helper::checkPermission(['vehicle-list','vehicle-create','vehicle-edit','vehicle-delete']);
                    @endphp
                    @if($vehiclePermission)
                    <li class=" @if(Request::segment('2') == 'vehiclecategories') active @endif">
                        <a href="{{ route('admin.vehicleindex') }}" data-toggle="tooltip" title="Vehicle Management">Ranking</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            

            @php
            $driverdocumentsPermission = \App\Helpers\Helper::checkPermission(['driver-document-list']);
            $vehicledocumentsPermission = \App\Helpers\Helper::checkPermission(['vehicle-document-list']);
            @endphp
            @if($driverdocumentsPermission || $vehicledocumentsPermission)
            <li class="@if(Request::segment('2') == 'driver_document') active @endif @if(Request::segment('2') == 'vehciledoc') active @endif">
                <a href="#">
                    <i class="fa fa-file-word-o"></i>
                    <span class="nav-label">Documents</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @php
                    $driverdocumentsPermission = \App\Helpers\Helper::checkPermission(['driver-document-list']);
                    @endphp
                    @if($driverdocumentsPermission)
                    <li class="@if(Request::segment('2') == 'driver_document') active @endif">
                        <a href="{{ route('admin.driverdoc.index') }}" data-toggle="tooltip" title="Driver Document"> Driver Document
                            <span class="label label-info float-right">
                                {{$u = \DB::table('driver_documents')
                                ->join('users', 'users.id', '=', 'driver_documents.driver_id')
                                ->select('driver_documents.*', 'users.doc_status')
                                ->groupBy('driver_documents.driver_id')
                                ->where('users.doc_status','pending')
                                ->where('driver_documents.driver_id','!=','')
                                ->where('driver_documents.deleted_at',null)
                                ->get()->count()}}
                            </span>
                        </a>
                    </li>
                    @endif

                    @php
                    $vehicledocumentsPermission = \App\Helpers\Helper::checkPermission(['vehicle-document-list']);
                    @endphp
                    @if($vehicledocumentsPermission)
                    <li class="@if(Request::segment('2') == 'vehciledoc') active @endif">
                        <a href="{{ route('admin.vehciledoc.index') }}" data-toggle="tooltip" title="Vehicle Document">Vehicle Document
                            <span class="label label-info float-right">
                                {{$u = \DB::table('driver_vehicle_documents')
                                ->join('users', 'users.id', '=', 'driver_vehicle_documents.driver_id')
                                ->select('driver_vehicle_documents.*', 'users.vehicle_doc_status')
                                ->groupBy('driver_vehicle_documents.driver_id')
                                ->where('users.vehicle_doc_status','pending')
                                ->where('driver_vehicle_documents.driver_id','!=','')
                                ->where('driver_vehicle_documents.deleted_at',null)
                                ->get()->count()
                            }}
                        </span>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        @php
        $rolePermission = \App\Helpers\Helper::checkPermission(['role-list','role-edit']);
        @endphp
        @if($rolePermission)

        <li class="@if(Request::segment('2') == 'role') active @endif">
            <a href="{{ route('admin.role.index') }}" data-toggle="tooltip" title="Sub Admin Management">
                <i class="fa fa-tasks"></i>
                <span class="nav-label">Sub Admin Management</span>
            </a>
        </li>
        @endif

        @php
        $statesPermission = \App\Helpers\Helper::checkPermission(['states-list','states-edit','states-create','states-delete']);

        $cityPermission = \App\Helpers\Helper::checkPermission(['city-list','city-edit','city-create','city-delete']);
        @endphp
        @if($statesPermission || $cityPermission)
        <li class="@if(Request::segment('2') == 'states') active @endif @if(Request::segment('2') == 'cities') active @endif">
            <a href="#">
                <i class="fa fa-flag"></i>
                <span class="nav-label">Country Management</span>
                <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
                @php
                $statesPermission = \App\Helpers\Helper::checkPermission(['states-list','states-edit','states-create','states-delete']);
                @endphp
                @if($statesPermission)
                <li class=" @if(Request::segment('2') == 'states') active @endif">
                    <a href="{{ route('admin.states.index') }}" data-toggle="tooltip" title="State">
                        <span class="nav-label">State</span>
                    </a>
                </li>
                @endif

                @php
                $cityPermission = \App\Helpers\Helper::checkPermission(['city-list','city-edit','city-create','city-delete']);
                @endphp
                @if($cityPermission)
                <li class=" @if(Request::segment('2') == 'cities') active @endif">
                    <a href="{{ route('admin.states.city_index') }}" data-toggle="tooltip" title="City">
                        <span class="nav-label">City</span>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif 

        @php
        $ridesettingPermission = \App\Helpers\Helper::checkPermission(['ridesetting-list','ridesetting-edit']);
        @endphp
        @if($ridesettingPermission)
        <li class="@if(Request::segment('2') == 'ridesetting') active @endif">
            <a href="{{ route('admin.ridesetting') }}" data-toggle="tooltip" title="Ride Setting">
                <i class="fa fa-users"></i>
                <span class="nav-label">Ride Setting</span>
            </a>
        </li>
        @endif

        @php
        $bookingPermission = \App\Helpers\Helper::checkPermission(['booking-list']);

        $parcelPermission = \App\Helpers\Helper::checkPermission(['parcel-list']);

        $shuttleridePermission = \App\Helpers\Helper::checkPermission(['shuttle-ride-list']);
        @endphp
        @if($bookingPermission || $parcelPermission || $shuttleridePermission)
        <li class="@if(Request::segment('2') == 'trip') active @endif @if(Request::segment('2') == 'parcels') active @endif  @if(Request::segment('2') == 'lineride') active @endif">
            <a href="#">
                <i class="fa fa-book"></i>
                <span class="nav-label">Booking Management</span>
                <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
                @php
                $bookingPermission = \App\Helpers\Helper::checkPermission(['booking-list']);
                @endphp
                @if($bookingPermission)
                <li class="@if(Request::segment('2') == 'trip') active @endif">
                    <a href="{{ route('admin.tripindex') }}" data-toggle="tooltip" title="Ride Booking"> Ride Booking</a>
                </li>
                @endif

                @php
                $parcelPermission = \App\Helpers\Helper::checkPermission(['parcel-list']);
                @endphp
                @if($parcelPermission)
                <li class="@if(Request::segment('2') == 'parcels') active @endif">
                    <a href="{{ route('admin.parcels.index') }}" data-toggle="tooltip" title="Parcel Booking">Parcel Booking</a>
                </li>
                @endif

                @php
                $shuttleridePermission = \App\Helpers\Helper::checkPermission(['shuttle-ride-list']);
                @endphp
                @if($shuttleridePermission)
                <li class="@if(Request::segment('2') == 'lineride') active @endif">
                    <a href="{{ route('admin.lineride.index') }}" data-toggle="tooltip" title="Shuttle Ride Booking">Shuttle Ride Booking</a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        @php
        $reviewratingPermission = \App\Helpers\Helper::checkPermission(['review-rating-list','review-rating-delete']);
        @endphp
        @if($reviewratingPermission)
        <li class="@if(Request::segment('2') == 'reviewrating') active @endif">
            <a href="{{ route('admin.reviewindex') }}" data-toggle="tooltip" title="Review And Rating">
                <i class="fa fa-star"></i>
                <span class="nav-label">Review And Rating</span>
            </a>
        </li>
        @endif

        @php
        $emergencycontactPermission = \App\Helpers\Helper::checkPermission(['emergencycontact-list','emergencycontact-edit','emergencycontact-create','emergencycontact-delete']);
        @endphp
        @if($emergencycontactPermission)
        <li class="@if(Request::segment('2') == 'emergency') active @endif">
            <a href="{{ route('admin.emergencyindex') }}" data-toggle="tooltip" title="Emergency Contact">
                <i class="fa fa-ambulance"></i>
                <span class="nav-label">Emergency Contact</span>
            </a>
        </li>
        @endif

        @php
        $transactionPermission = \App\Helpers\Helper::checkPermission(['transaction-list']);
        @endphp
        @if($transactionPermission)
        <li class=" @if(Request::segment('2') == 'transaction_detail') active @endif">
            <a href="{{ route('admin.transaction_detail.index') }}" data-toggle="tooltip" title="Transaction Details">
                <i class="fa fa-money"></i>
                <span class="nav-label">Transaction Details</span>
            </a>
        </li>
        @endif

        @php
        $userreferralPermission = \App\Helpers\Helper::checkPermission(['user-referral-list']);
        $driverreferralPermission = \App\Helpers\Helper::checkPermission(['driver-referral-list']);
        @endphp
        @if($userreferralPermission || $driverreferralPermission)
        <li class="@if(Request::segment('2') == 'driver_referral') active @endif @if(Request::segment('2') == 'user_referral') active @endif">
            <a href="#">
                <i class="fa fa-user-plus"></i>
                <span class="nav-label">Referral Management</span>
                <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
                @php
                $userreferralPermission = \App\Helpers\Helper::checkPermission(['user-referral-list']);
                @endphp
                @if($userreferralPermission)
                <li class="@if(Request::segment('2') == 'user_referral') active @endif">
                    <a href="{{ route('admin.user_ref.index') }}" data-toggle="tooltip" title="User Referral">User Referral</a>
                </li>
                @endif

                @php
                $driverreferralPermission = \App\Helpers\Helper::checkPermission(['driver-referral-list']);
                @endphp
                @if($driverreferralPermission)
                <li class="@if(Request::segment('2') == 'driver_referral') active @endif">
                    <a href="{{ route('admin.driver_ref.index') }}" data-toggle="tooltip" title="Driver Referral">Driver Referral</a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        @php
        $promocodePermission = \App\Helpers\Helper::checkPermission(['promocode-list','promocode-edit','promocode-create','promocode-delete']);
        @endphp
        @if($promocodePermission)
        <li class="@if(Request::segment('2') == 'promocode') active @endif">
            <a href="{{ route('admin.promocode.index') }}" data-toggle="tooltip" title="Promocode">
                <i class="fa fa-gift"></i>
                <span class="nav-label">Promocode</span>
            </a>
        </li>
        @endif

        <li class="@if(Request::segment('2') == 'join_network') active @endif">
            <a href="{{ route('admin.join_network.index') }}" data-toggle="tooltip" title="join network">
                <i class="fa fa-gift"></i>
                <span class="nav-label">Join Network List</span>
            </a>
        </li>



        @php
        $userstarratingPermission = \App\Helpers\Helper::checkPermission(['user-star-rating-list']);
        $driverstarratingPermission = \App\Helpers\Helper::checkPermission(['driver-star-rating-list']);
        @endphp
        @if($userstarratingPermission || $driverstarratingPermission)
        <li class="@if(Request::segment('2') == 'user_star_rating') active @endif @if(Request::segment('2') == 'driver_star_rating') active @endif">
            <a href="#">
                <i class="fa fa-star"></i>
                <span class="nav-label">Star Rating</span>
                <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
                @php
                $userstarratingPermission = \App\Helpers\Helper::checkPermission(['user-star-rating-list']);
                @endphp
                @if($userstarratingPermission)
                <li class="@if(Request::segment('2') == 'user_star_rating') active @endif">
                    <a href="{{ route('admin.user_star.index') }}" data-toggle="tooltip" title="User Star Rating">User Star Rating</a>
                </li>
                @endif

                @php
                $driverstarratingPermission = \App\Helpers\Helper::checkPermission(['driver-star-rating-list']);
                @endphp
                @if($driverstarratingPermission)
                <li class="@if(Request::segment('2') == 'driver_star_rating') active @endif">
                    <a href="{{ route('admin.driver_star.index') }}" data-toggle="tooltip" title="Driver Star Rating">Driver Star Rating</a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        @php
        $notificationPermission = \App\Helpers\Helper::checkPermission(['notification-list']);
        @endphp
        @if($notificationPermission)
        <li class="@if(Request::segment('2') == 'notification') active @endif">
            <a href="{{ route('admin.notification.index') }}" data-toggle="tooltip" title="Notification">
                <i class="fa fa-bell"></i>
                <span class="nav-label">Notification</span>
            </a>
        </li>
        @endif

        @php
        $cmsPermission = \App\Helpers\Helper::checkPermission(['cms-list','cms-edit']);
        @endphp
        @if($cmsPermission)
        <li class=" @if(Request::segment('2') == 'cms') active @endif">
            <a href="{{ route('admin.cms.index') }}" data-toggle="tooltip" title="CMS Pages">
                <i class="fa fa-file-o"></i>
                <span class="nav-label">CMS Pages</span>
            </a>
        </li>
        @endif

        @php
        $supportPermission = \App\Helpers\Helper::checkPermission(['support-list','support-edit']);
        @endphp
        @if($supportPermission)
        <li class=" @if(Request::segment('2') == 'support') active @endif">
            <a href="{{ route('admin.support.index') }}" data-toggle="tooltip" title="Support Management">
                <i class="fa fa-envelope"></i>
                <span class="nav-label">Support Management</span>
            </a>
        </li>
        @endif

        @php
        $reportsPermission = \App\Helpers\Helper::checkPermission(['reports-list']);
        @endphp
        @if($reportsPermission)
        <li class=" @if(Request::segment('2') == 'reports_detail') active @endif">
            <a href="{{ route('admin.transaction_detail.reports_detail') }}" data-toggle="tooltip" title="Reports">
                <i class="fa fa-file"></i>
                <span class="nav-label">Reports</span>
            </a>
        </li>
        @endif

        @php
        $locationPermission = \App\Helpers\Helper::checkPermission(['location-list']);
        @endphp
        @if($locationPermission)
        <li class=" @if(Request::segment('2') == 'locations') active @endif">
            <a href="{{ route('admin.location_index') }}" data-toggle="tooltip" title="Location">
                <i class="fa fa-map-marker"></i>
                <span class="nav-label">Location</span>
            </a>
        </li>
        @endif

        @php
        $behaviorPermission = \App\Helpers\Helper::checkPermission(['behavior-list','behavior-edit','behavior-create','behavior-delete']);

        $emergencytypesPermission = \App\Helpers\Helper::checkPermission(['emergencytypes-list','emergencytypes-edit','emergencytypes-create','emergencytypes-delete']);

        $emergencyrequestPermission = \App\Helpers\Helper::checkPermission(['emergencyrequest-list']);

        $userreportPermission = \App\Helpers\Helper::checkPermission(['userreport-list']);


        @endphp

        @if($behaviorPermission  || $emergencytypesPermission || $emergencyrequestPermission || $userreportPermission)
        <li class="@if(Request::segment('2') == 'behaviors') active @endif @if(Request::segment('2') == 'emergencytypes') active @endif @if(Request::segment('3') == 'emergencytypeedit') active @endif @if(Request::segment('2') == 'emergencyrequest') active @endif @if(Request::segment('2') == 'userreports') active @endif">
            <a href="#">
                <i class="fa fa-gear"></i>
                <span class="nav-label">General Settings</span>
                <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level collapse">
                @php
                $behaviorPermission = \App\Helpers\Helper::checkPermission(['behavior-list','behavior-edit','behavior-create','behavior-delete']);
                @endphp
                @if($behaviorPermission)
                <li class="@if(Request::segment('2') == 'behaviors') active @endif">
                    <a href="{{ route('admin.behaviors.index') }}" data-toggle="tooltip" title="Behaviors">Behaviors</a>
                </li>
                @endif

                @php
                $emergencytypesPermission = \App\Helpers\Helper::checkPermission(['emergencytypes-list','emergencytypes-edit','emergencytypes-create','emergencytypes-delete']);
                @endphp
                @if($emergencytypesPermission)
                <li class="@if(Request::segment('2') == 'emergencytypes' || Request::segment('3') == 'emergencytypeedit') active @endif">
                    <a href="{{ route('admin.emergencytypes') }}" data-toggle="tooltip" title="Emergency Types">Emergency Types</a>
                </li>
                @endif

                @php
                $emergencyrequestPermission = \App\Helpers\Helper::checkPermission(['emergencyrequest-list']);
                @endphp
                @if($emergencyrequestPermission)
                <li class=" @if(Request::segment('2') == 'emergencyrequest') active @endif">
                    <a href="{{ route('admin.emergencyrequest') }}" data-toggle="tooltip" title="Emergency Request">Emergency Request</a>
                </li>
                @endif

                @php
                $userreportPermission = \App\Helpers\Helper::checkPermission(['userreport-list']);
                @endphp
                @if($userreportPermission)
                <li class=" @if(Request::segment('2') == 'userreports') active @endif">
                    <a href="{{ route('admin.userreports') }}" data-toggle="tooltip" title="User Report">User Report</a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        @php
        $settings = \App\Helpers\Helper::checkPermission(['setting-list','setting-edit']);
        @endphp
        @if($settings)
        <li class="@if(Request::segment('2') == 'settings') active @endif">
            <a href="{{ url('admin/settings') }}" data-toggle="tooltip" title="Site Settings">
                <i class="fa fa-cogs"></i>
                <span class="nav-label">Site Settings</span>
            </a>
        </li>
        @endif
    </ul>
</div>
</nav>
