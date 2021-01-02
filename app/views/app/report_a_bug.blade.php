@if(Auth::user()->isUserTest())
</div>

<div>
    <br/>
</div>
<h1>Report a bug</h1>

<div class="admin2">

    @include('includes.notifications')

    <ul class="nav nav-tabs" style="margin: 30px 0">
        <li class="active"><a href="#snugzone" data-toggle="tab">SnugZone</a></li>
        <li><a href="#prepaygo" data-toggle="tab">PrepayGO</a></li>
    </ul>

    <div class="tab-content">

        <div class="tab-pane active" id="snugzone" style="text-align: left">
		
		
            <h4> UnSolved </h4>
            <table width="100%" class="table table-bordered">

                <tr>
                    <th width="25%">Created</th>
                    <th width="15%">Customer</th>
                    <th widht="10">Preview</th>
                    <th width="30%">Manage</th>
                </tr>

                @foreach($bugs as $b)

                <tr @if($b->resolved) style="background:#a8e2a8;" @endif>
                    <td>{{ $b->created_at }} &horbar; {{ \Carbon\Carbon::parse($b->created_at)->diffForHumans() }}</td>

                    @if($b->customer)
                    <td><a href="/customer/{{ $b->customer->username }}">{{ $b->customer->username }}</a></td>
                    @else
                    <td><a href="/customer/{{ $b->apt_number }}{{ $b->apt_building }}" target="_blank">
			{{ $b->apt_number }}{{ $b->apt_building }}
			(g)
		</a></td>
                    @endif

                      <td>{{ substr($b->description, 0, 50) }}</td>
                    <td>
                        <a href="{{ URL::to('bug/reports/view', ['id' => $b->id]) }}">
                            <button class="btn btn-primary"><i class="fa fa-eye"></i> View</button>
                        </a>
                        <a href="{{ URL::to('bug/reports/view', ['id' => $b->id, 'solved' => 1]) }}">
                            <button class="btn btn-success"><i class="fa fa-check"></i> Complete</button>
                        </a>
                        
                    </td>
                </tr>

                @endforeach

            </table>

            <hr/>

            <h4> Solved </h4>

            <table width="100%" class="table table-bordered">

                <tr>
                    <th width="25%">Created</th>
                    <th width="15%">Customer</th>
                    <th widht="10">Preview</th>
                    <th width="30%">Manage</th>
                </tr>

                @foreach($solved_bugs as $b)

                <tr @if($b->resolved) @if(strlen($b->follow_up_at) > 3 && !$b->follow_up_res) style="background:#e2a8a8;" @else style="background:#a8e2a8;"  @endif @endif>
                    <td>{{ $b->created_at }} &horbar; {{ \Carbon\Carbon::parse($b->created_at)->diffForHumans() }}</td>

                    @if($b->customer)
                    <td><a href="/customer/{{ $b->customer->username }}">{{ $b->customer->username }}</a></td>
                    @else
                    <td><a href="/customer/{{ $b->apt_number }}{{ $b->apt_building }}" target="_blank">
							{{ $b->apt_number }}{{ $b->apt_building }}
							(g)
						</a></td>
                    @endif

                    <td>{{ substr($b->description, 0, 50) }}</td>
                    <td>
                        <a href="{{ URL::to('bug/reports/view', ['id' => $b->id]) }}">
                            <button class="btn btn-primary"><i class="fa fa-eye"></i> View</button>
                        </a>
                      @if(strlen($b->follow_up_at) > 3)
						  &nbsp;&nbsp;<span class="badge badge-success">Provided feedback 📧</span>
					  @else
						  @if(($b->follow_up_sent || $b->follow_up_sent_2))
								&nbsp;&nbsp;<span class="badge badge-secondary">Awaiting feedback...</span>
						  @else
								&nbsp;&nbsp;<span class="badge badge-info">No follow-up sent yet</span>
						  @endif
					  @endif
                    </td>
                </tr>

                @endforeach

            </table>
        </div>

        <div class="tab-pane" id="prepaygo" style="text-align: left">
            <table width="100%" class="table table-bordered">

                <tr>
                    <th width="25%">Created</th>
                    <th width="15%">Customer</th>
                    <th widht="10">Desc</th>
                    <th width="30%">Manage</th>
                </tr>

                @foreach($bugs_prepaygo as $b)

                <tr @if($b->resolved) style="background:#a8e2a8;" @endif>
                    <td>{{ $b->created_at }} &horbar; {{ \Carbon\Carbon::parse($b->created_at)->diffForHumans() }}</td>

                    @if($b->customer)
                    <td><a href="/customer/{{ $b->customer->username }}">{{ $b->customer->username }}</a></td>
                    @else
                    <td><a href="/customer/{{ $b->apt_number }}{{ $b->apt_building }}" target="_blank">
			{{ $b->apt_number }}{{ $b->apt_building }}
			(g)
		</a></td>
                    @endif

                    <td>{{ $b->preview }}</td>
                    <td>
                        <a href="{{ URL::to('bug/reports/view', ['id' => $b->id, 'platform' => 'prepaygo']) }}">
                            <button class="btn btn-primary"><i class="fa fa-eye"></i> View</button>
                        </a>
                        <a href="{{ URL::to('bug/reports/view', ['id' => $b->id, 'solved' => 1, 'platform' => 'prepaygo']) }}">
                            <button class="btn btn-success"><i class="fa fa-check"></i> Complete</button>
                        </a>
                        
                    </td>
                </tr>

                @endforeach

            </table>

            <hr/>

            <h4> Solved </h4>

            <table width="100%" class="table table-bordered">

                <tr>
                   <th width="25%">Created</th>
                    <th width="15%">Customer</th>
                    <th widht="10">Desc</th>
                    <th width="30%">Manage</th>
                </tr>

                @foreach($solved_bugs_prepaygo as $b)

                <tr @if($b->resolved) style="background:#a8e2a8;" @endif>
                    <td>{{ $b->created_at }} &horbar; {{ \Carbon\Carbon::parse($b->created_at)->diffForHumans() }}</td>

                    @if($b->customer)
                    <td><a href="/customer/{{ $b->customer->username }}">{{ $b->customer->username }}</a></td>
                    @else
                    <td><a href="/customer/{{ $b->apt_number }}{{ $b->apt_building }}" target="_blank">
							{{ $b->apt_number }}{{ $b->apt_building }}
							(g)
						</a></td>
                    @endif

                    <td>{{ $b->preview }}</td>
                    <td>
                        <a href="{{ URL::to('bug/reports/view', ['id' => $b->id, 'platform' => 'prepaygo']) }}">
                            <button class="btn btn-primary"><i class="fa fa-eye"></i> View</button>
                        </a>
						@if(strlen($b->follow_up_at) > 3)
						  &nbsp;&nbsp;<span class="badge badge-success">Provided feedback</span>
						  @else
							  
						  @endif
                        
                    </td>
                </tr>

                @endforeach

            </table>
        </div>

    </div>

</div>
@else
	<br/><br/>
		<h2>Access Denied.</h2>
	<br/><br/>
@endif