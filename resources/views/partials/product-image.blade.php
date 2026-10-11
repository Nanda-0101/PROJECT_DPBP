@php
  $img = \App\Helpers\ImageHelper::url($imagePath ?? $p->image ?? null);
  $imageName = $imageAlt ?? $p->name ?? 'Produk CamRent';
  $imageIcon = $imageIcon ?? $p->icon ?? 'bi-box-seam';
@endphp
<div class="pc-image-wrap">
  @if($img)
    <img class="pc-photo" src="{{ $img }}" alt="{{ $imageName }}" loading="lazy"
         onerror="this.hidden=true;this.nextElementSibling.hidden=false">
  @endif
  <div class="pc-icon pc-image-fallback" @if($img) hidden @endif>
    <i class="bi {{ $imageIcon }}"></i>
  </div>
</div>
