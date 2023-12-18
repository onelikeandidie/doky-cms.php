/** @type {import('tailwindcss').Config} */
import plugin from 'tailwindcss/plugin';

// https://stackoverflow.com/a/77121542
const radialGradientPlugin = plugin(
    function ({ matchUtilities, theme }) {
        matchUtilities(
            {
                // map to bg-radient-[*]
                'bg-radient': value => ({
                    'background-image': `radial-gradient(${value},var(--tw-gradient-stops))`,
                }),
            },
            { values: theme('radialGradients') }
        )
    },
    {
        theme: {
            radialGradients: _presets(),
        },
    }
)

/**
 * utility class presets
 */
function _presets() {
    const shapes = ['circle', 'ellipse'];
    const pos = {
        c: 'center',
        t: 'top',
        b: 'bottom',
        l: 'left',
        r: 'right',
        tl: 'top left',
        tr: 'top right',
        bl: 'bottom left',
        br: 'bottom right',
    };
    let result = {};
    for (const shape of shapes)
        for (const [posName, posValue] of Object.entries(pos))
            result[`${shape}-${posName}`] = `${shape} at ${posValue}`;

    return result;
}

export default {
    prefix: "tw-",
    darkMode: 'class',
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
    ],
    theme: {
        extend: {},
    },
    safelist: [
        // This is on the safelist because it is used in the markdown editor
        // Include text sizes
        'tw-text-xs',
        'tw-text-sm',
        'tw-text-base',
        'tw-text-lg',
        'tw-text-xl',
        'tw-text-2xl',
        'tw-text-3xl',
        'tw-text-4xl',
        'tw-text-5xl',
        'tw-text-6xl',
        'tw-text-7xl',
        'tw-text-8xl',
        'tw-text-9xl',
        'tw-text-h1',
        'tw-text-h2',
        'tw-text-h3',
        'tw-text-h4',
        'tw-text-h5',
        // Include text positions
        'tw-text-center',
        // Include list styles
        'tw-list-none',
        'tw-list-disc',
        'tw-list-decimal',
        'tw-list-inside',
        'tw-list-outside',
        // Include display styles
        'tw-block',
        'tw-inline-block',
        'tw-inline',
        'tw-flex',
        // Include margin styles
        'tw-pl-4',
        // Include font weights
        'tw-font-thin',
        'tw-font-extralight',
        'tw-font-light',
        'tw-font-normal',
        'tw-font-medium',
        'tw-font-semibold',
        'tw-font-bold',
        'tw-font-extrabold',
        // Include some padding lefts for the file trees
        'tw-pl-2',
        'tw-pl-4',
        'tw-pl-6',
        'tw-pl-8',
        'tw-pl-10',
        // Include some padding tops for the file trees
        'tw-mt-2',
        'tw-mb-4',
        'tw-my-4',
        'tw-pb-2',
        // Aspect ratio
        'tw-aspect-w-16',
        'tw-aspect-h-9',
        //
        'md:tw-w-4/5',
        'lg:tw-w-3/5',
        'xl:tw-w-2/5',
        'tw-mx-auto',
    ],
    plugins: [
        // Plugin for aspect ratio
        require('@tailwindcss/aspect-ratio'),
        radialGradientPlugin,
    ],
}

