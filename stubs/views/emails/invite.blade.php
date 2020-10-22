You have been invited to join group {{$group->name}}.<br>
Click here to join: <a href="{{route('groups.accept_invite', $invite->accept_token)}}">{{route('groups.accept_invite', $invite->accept_token)}}</a>
