const Encore = require('@symfony/webpack-encore')
const path = require('path')

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')

    // public path used by the web server to access the output path
    .setPublicPath('/build')

    .addEntry('app', './assets/js/app.js')

    .addEntry('traditional/account/index', './assets/js/traditional/account/index.js')
    .addEntry('traditional/agent/show', './assets/js/traditional/agent/show.js')
    .addEntry('traditional/agent/edit', './assets/js/traditional/agent/edit.js')
    .addEntry('traditional/ingress_event/overview', './assets/js/traditional/ingress_event/overview.js')
    .addEntry('traditional/map/index', './assets/js/traditional/map/index.js')
    .addEntry('traditional/stats/agent-stats', './assets/js/traditional/stats/agent-stats.js')
    .addEntry('traditional/stats/leaderboard', './assets/js/traditional/stats/leaderboard.js')
    .addEntry('traditional/user/edit', './assets/js/traditional/user/edit.js')

    // Vue
    .addEntry('vue/agents', './assets/js/vue/agents.js')
    .addEntry('vue/users', './assets/js/vue/users.js')

    // Helper
    .addEntry('helper/editor', './assets/js/helper/editor.js')
    .addEntry('helper/events', './assets/js/helper/events.js')
    .addEntry('helper/paginator', './assets/js/helper/paginator.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    // This is our alias to the root vue components dir
    .addAliases({
        '@': path.resolve(__dirname, 'assets', 'js'),
        styles: path.resolve(__dirname, 'assets', 'scss'),
    })

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()
    .enablePostCssLoader()

    .enableVueLoader()

    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]',
    })
;

module.exports = Encore.getWebpackConfig();
