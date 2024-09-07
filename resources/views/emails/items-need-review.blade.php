@component('mail::message')
# Discount Items Need Review

Some new discount items have been flagged for review.

Image Path: {{ $imagePath }}

@component('mail::table')
| Item | Original Price | Discount % | Discounted Price |
| ---- | -------------- | ---------- | ---------------- |
@foreach($items as $item)
| {{ $item->item }} | {{ $item->original_price }} | {{ $item->discount_percentage }} | {{ $item->discounted_price }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => route('admin.review-items')])
Review Items
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
