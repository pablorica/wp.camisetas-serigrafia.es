import babel from '@rollup/plugin-babel';
import vue from 'rollup-plugin-vue'
import nodeResolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import multi from '@rollup/plugin-multi-entry'


export default  {
    input: ['./admin/src/js/admin.js', './admin/src/js/vueAdmin.js'],
    //input: ['./admin/src/js/admin.js'],
    output: {
        file: './admin/js/cg-woo-grid-admin.js',
        format: 'umd',
        name: 'bundle'
    },
    plugins: [
        babel({
            // Only transpile our source code
            exclude: 'node_modules/**',
            // Include the helpers in the bundle, at most one copy of each. 
            // 'bundled' option is by default. It is recommended to configure this option explicitly, read more here: https://github.com/rollup/plugins/tree/master/packages/babel#babelhelpers
            babelHelpers: 'bundled'
        }),
        vue({
            template: {
                compilerOptions: {
                  // treat all components starting with `cdg` as custom elements
                  isCustomElement: tag => tag.startsWith('cdg'),
                },
              },
        }),
        nodeResolve(),
        commonjs(),
        multi(),
    ]
}
