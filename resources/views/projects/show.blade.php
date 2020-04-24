{{ $project->title }}

@if( $project->client->isNotEmpty() )
    {{ $project->client->name }}
@endif