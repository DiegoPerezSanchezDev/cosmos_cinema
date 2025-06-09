import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from '@vitejs/plugin-vue2';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/slider.css",
                "resources/js/registro.js",
                "resources/css/registro.css",
                "resources/js/login.js",
                "resources/css/compraEntradas.css",
                "resources/js/entradas.js",
                "resources/css/user_modal.css",
                "resources/css/adminLogin.css",
                "resources/js/cartaCosmos.js",
                "resources/css/cartaCosmos.css",
                "resources/js/user.js",
                "resources/js/loginAdmin.js",
                "resources/js/adminDashboard.js",
                "resources/css/dashboard.css",
                "resources/js/adminDashboardGestionarPelicula.js",
                "resources/js/adminDashboardGestionarMenu.js",
                "resources/js/adminDashboardSesiones.js",
                "resources/js/adminDashboardAñadirEmpleado.js",
                "resources/css/cartelera.css",
                'resources/css/detalle_pelicula.css',
                "resources/js/detalle_y_asientos.js",
                'resources/css/swiper-custom.css',
                'resources/js/slider-init.js',
                'resources/js/menu_hamburguesa.js',
                'resources/js/flash_mensaje.js',
                'resources/js/detalle_estreno.js',
                'resources/css/footer.css',
                'resources/css/footer_elemento.css',
                'resources/css/confirmar_seleccion.css',
                'resources/css/invitado.css',
                'resources/css/menu_hamburguesa.css',
                'resources/css/errors.css',
            ], 
            refresh: true,
            base: "./",
        }),
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm.js',
        },
    },
    build: {
        outDir: "public/build",
        manifest: true,
    },
});