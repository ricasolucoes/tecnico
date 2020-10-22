@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading clearfix">
                        Groups
                        <a class="pull-right btn btn-default btn-sm" href="{{route('groups.create')}}">
                            <i class="fa fa-plus"></i> Create group
                        </a>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groups as $group)
                                    <tr>
                                        <td>{{$group->name}}</td>
                                        <td>
                                            @if(auth()->user()->isOwnerOfGroup($group))
                                                <span class="label label-success">Owner</span>
                                            @else
                                                <span class="label label-primary">Member</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_null(auth()->user()->currentGroup) || auth()->user()->currentGroup->getKey() !== $group->getKey())
                                                <a href="{{route('groups.switch', $group)}}" class="btn btn-sm btn-default">
                                                    <i class="fa fa-sign-in"></i> Switch
                                                </a>
                                            @else
                                                <span class="label label-default">Current group</span>
                                            @endif

                                            <a href="{{route('groups.members.show', $group)}}" class="btn btn-sm btn-default">
                                                <i class="fa fa-users"></i> Members
                                            </a>

                                            @if(auth()->user()->isOwnerOfGroup($group))

                                                <a href="{{route('groups.edit', $group)}}" class="btn btn-sm btn-default">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>

                                                <form style="display: inline-block;" action="{{route('groups.destroy', $group)}}" method="post">
                                                    {!! csrf_field() !!}
                                                    <input type="hidden" name="_method" value="DELETE" />
                                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i> Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
