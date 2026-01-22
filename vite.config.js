import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // host: "0.0.0.0",
        // port: 5173,
        // strictPort: true,
        // // https: {
        // //     key: fs.readFileSync("localhost-key.pem"),
        // //     cert: fs.readFileSync("localhost.pem"),
        // // },
        // // hmr: {
        // //     host: "dodecastyle-trenchant-rutha.ngrok-free.dev",
        // //     protocol: "wss",
        // // },
        // proxy: {
        //     "/": {
        //         target: "http://127.0.0.1:8000",
        //         changeOrigin: true,
        //     },
        // },
        //   },
        cors: true,
    },
});
