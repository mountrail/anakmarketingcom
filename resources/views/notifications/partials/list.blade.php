{{-- resources/views/notifications/partials/list.blade.php --}}
<div class="space-y-0">
    @foreach ($notifications as $notification)
        @include('notifications.partials.item', ['notification' => $notification])
    @endforeach
</div>
