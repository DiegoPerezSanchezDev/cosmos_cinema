<div class="container_menu">
    <div class='cartelera-titulo'>
        <h3>Nuestra Carta Estelar</h3>
    </div>
    <div class='separador'></div>
    <div class="menus-grid">
        {{-- ****** Bucle para mostrar cada menú de la base de datos ****** --}}
        @foreach($menus as $menu)
            <div class="menu-item">
                <div class="menu-item-inner">
                    {{-- Cara frontal: Imagen y Nombre --}}
                    <div class="menu-item-front">
                        @if(isset($menu->imagen_url) && $menu->imagen_url)
                            <img src="{{ asset($menu->imagen_url) }}" alt="{{ $menu->nombre }}" class="menu-item-image">
                        @else
                            <img src="{{ asset('images/placeholder-menu.jpg') }}" alt="{{ $menu->nombre }} - Sin imagen" class="menu-item-image">
                        @endif

                        <h3 class="menu-item-name">{{ $menu->nombre }}</h3>
                    </div>
                    {{-- Cara trasera: Descripción y Precio --}}
                    
                    <div class="menu-item-back">
                        <p class="menu-item-description">{{ $menu->descripcion }}</p>
                        <p class="menu-item-price">{{ number_format($menu->precio, 2) }}€</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>