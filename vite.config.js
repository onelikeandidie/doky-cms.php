import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import * as fs from "fs";

function readDirRecursive(dir) {
    let results = [];
    let files = fs.readdirSync(dir);
    for (let file of files) {
        file = dir + '/' + file;
        let stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(readDirRecursive(file))
        } else {
            results.push(file)
        }
    }
    return results;
}

// Get every file in resources/js/pages/**/*
let pageFiles = readDirRecursive('resources/js/pages');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                ...pageFiles,
            ],
            refresh: true,
        }),
    ],
});
