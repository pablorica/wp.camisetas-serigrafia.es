const { promises: fs } = require("fs")
const path = require("path")

const replace = require('replace-in-file');




async function copyDir(src, dest) {
    await fs.mkdir(dest, { recursive: true });
    let entries = await fs.readdir(src, { withFileTypes: true });
	let ignore = [
		'node_modules',
		'dist',
		'src',
		'.github',
		'.browserslistrc',
		'.editorconfig',
		'.gitattributes',
		'.gitignore',
		'.jscsrc',
		'.jshintignore',
		'.travis.yml',
        '.babelrc',
        '.DS_Store',
		'composer.json',
		'composer.lock',
		'package.json',
		'package-lock.json',
		'phpcs.xml.dist',
		'readme.txt',
        '.orig'
	];
    let ignoreexp = [
        '.orig',
        '.tar.gz',
	];


    for (let entry of entries) {
		if ( ignore.indexOf( entry.name ) != -1 ) {
			continue;
		}

        const contains = ignoreexp.some(element => {
            if (entry.name.includes(element)) {
                return true;
            }
            
            return false;
        });
        if ( contains) {
			continue;
		}
        
        let srcPath = path.join(src, entry.name);
        let destPath = path.join(dest, entry.name);


        entry.isDirectory() ?
            await copyDir(srcPath, destPath) :
            await fs.copyFile(srcPath, destPath);    
            
    }

    
}

copyDir('./', './dist')
    .then(res => {
        console.log('dist folder ready');
        //updating links to get the minified files
        const options = {
            files: './dist/public/class-cg-woo-grid-public.php',
            from:  [/cg-woo-grid\.js/g, /cg-woo-grid\.css/g],
            to: ['cg-woo-grid.min.js', 'cg-woo-grid.min.css'] ,
        };
        
        replace(options)
            .then(results => {
                console.log('Replacement results:', results);
            })
            .catch(error => {
                console.error('Error occurred:', error);
            });
    })
    .catch(error => {
        console.error('Error occurred:', error);
    });


