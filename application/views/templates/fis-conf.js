/**
 * Created by irvin on 16/7/5.
 */
fis.require('smarty')(fis);
fis.set('namespace', 'welcome');
fis.set('smarty', {
    'left_delimiter': '{%',
    'right_delimiter': '%}'
});
// default media is `dev`，
fis.media('dev').match('*', {
    useHash: false,
    optimizer: null
});

// 加 md5
fis.match('*.{js,css,png}', {
    useHash: true
});

// 启用 fis-spriter-csssprites 插件
fis.match('::package', {
    spriter: fis.plugin('csssprites')
})

// 对 CSS 进行图片合并
fis.match('*.css', {
    // 给匹配到的文件分配属性 `useSprite`
    useSprite: true
});

fis.match('*.js', {
    // fis-optimizer-uglify-js 插件进行压缩，已内置
    optimizer: fis.plugin('uglify-js')
});

fis.match('*.css', {
    // fis-optimizer-clean-css 插件进行压缩，已内置
    optimizer: fis.plugin('clean-css')
});

fis.match('*.png', {
    // fis-optimizer-png-compressor 插件进行压缩，已内置
    optimizer: fis.plugin('png-compressor')
});

//直接本地发布:
fis.match('*', {
    deploy: fis.plugin('local-deliver', {
        to: '/var/www/html/www.spir.com/'
    })
});
//模板
fis.match('*.tpl', {
    deploy: fis.plugin('local-deliver', {
        to: '/var/www/html/www.spir.com/' // to = $to + $file.release
    })
});
//map.json
fis.match('*-map.json', {
    deploy: fis.plugin('local-deliver', {
        to: '/var/www/html/www.spir.com/application/source_map' // to = $to + $file.release
    })
});

