const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  mode: 'development',
  entry: {
    'js/app': './src/js/app.js',
    'js/inicio': './src/js/inicio.js',
    
    'js/auth/index': './src/js/auth/index.js',
    'js/usuarios/index': './src/js/usuarios/index.js',
    'js/entregas/index': './src/js/entregas/index.js',
    'js/inventario/index': './src/js/inventario/index.js',
    'js/solicitudes/index': './src/js/solicitudes/index.js',
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'public/build'),
    clean: true
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'styles.css'
    })
  ],
  module: {
    rules: [
      {
        test: /\.(c|sc|sa)ss$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          'css-loader',
          'sass-loader'
        ]
      },
      {
        test: /\.(png|svg|jpe?g|gif)$/,
        type: 'asset/resource',
        generator: {
          filename: 'images/[hash][ext][query]'
        }
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[hash][ext][query]'
        }
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.json']
  },
  devtool: 'source-map'
};