<?php
/**
 * Esta é um guia básico, visando apresentar a API versão 2.0 do Webcast, 
 * o serviço de gestão e distribuição de vídeos online da SambaTech.
 *
 * PHP version 5
 *
 * @category Media
 * @package  SambaTech_Liquid
 * @author   Bruno Thiago Leite Agutoli <brunotla1@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://docs.liquidplatform.com/
 */

/**
 * Esta é um guia básico, visando apresentar a API versão 2.0 do Webcast, 
 * o serviço de gestão e distribuição de vídeos online da SambaTech.
 *
 * @category Media
 * @package  SambaTech_Liquid
 * @author   Bruno Thiago Leite Agutoli <brunotla1@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version  Release: 1.2
 * @link     http://docs.liquidplatform.com/
 */
class Webcast
{
    /**
     * Domínio atual da api
     * 
     * @var string REST_DOMAIN
     */
    const API_DOMAIN  = 'http://fast.api.liquidplatform.com';

    /**
     * Versão atual da api
     * 
     * @var string API_VERSION
     */
    const API_VERSION = '2.0';

    /**
     * Chave de acesso a api.
     * 
     * @var string $api_key
     */
    private $_api_key;

    /**
     * Guarda consultas em memoria
     * 
     * @var array $_cache
     */
    private $_memoryCache = array();

    /**
     * Construtor da classe
     * 
     * @param string $api_Key (obrigatório)
     * 
     * @return void
     * @access public
     */
    public function __construct( $api_Key )
    {
        if ( ! extension_loaded('curl') ) {
            return false;
        }
        if ( empty($api_Key) ) {
            return false;
        }
        $this->_api_key = $api_Key;
    }

    /**
     * Passando um parâmetro boleano true
     * habilita o modo debug e false 
     * desabilita
     * 
     * @param boolean $display (obrigatório)
     * 
     * @return void null
     * @access public
     */
    public function debug( $display )
    {
        if ( $display ) {
            error_reporting(E_ALL);
        }
        ini_set("display_errors", $display);
    }

    /**
     * Lista as mídias para um dado projeto, e permite diversos filtros e 
     * parâmetros para retornar um conjunto específico de mídias.
     * 
     * Este método retorna até 50 resultados por chamada. Para obter mais de 
     * 50 resultados basta realizar uma “paginação”, através 
     * dos parâmetros first e limit.
     * 
     * No cabeçalho ele contém o parâmetro “totalMedias” com o total 
     * de mídias selecionadas naquela requisição, levando em consideração 
     * os filtros aplicados.
     * 
     * @return mixed 
     * @link http://docs.liquidplatform.com/2010/09/medias/
     * @access public
     */
    public function medias() 
    {
        $url = $this->_apiUrl() . '/medias/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna o número de mídias do projeto em questão. 
     * O projeto é identificado pela key passada como parâmetro.
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediascount/
     * @access public
     */
    public function mediasCount() 
    {
        $url = $this->_apiUrl() .'/medias/count/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna uma lista de objetos RatingSummary referentes às mídias do 
     * projeto. Ou seja, retorna informações de voto sobre as mídias do projeto.
     * 
     * Esta lista é composta por mídias que receberam votos a partir da 
     * data passada como parâmetro no campo lastModified.
     * 
     * A lista é ordenada pelo número total de votos das mídias.
     * 
     * @param array $options opcional
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediasratings/
     * @access public
     */
    public function mediasRatings( $options = array() ) 
    {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/medias/ratings/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) { 
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    /**
     * Retorna uma lista de objetos View referentes às mídias do projeto. 
     * Esta lista é composta por mídias que foram vistas a partir da 
     * data passada como parâmetro no campo lastModified.
     * A lista é ordenada pelo número total de visualizações das mídias.
     * 
     * @param array $options (opcional)
     * 
     * @example array(
     *      'first' => 0,
     *      'limit' => 10
     *  )
     * @return mixed objects array
     * @access public
     */
    public function mediasViews( $options = array() ) 
    {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/medias/views/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    /**
     * Retorna o objeto Media correspondente ao parâmetro mediaId. 
     * O escopo da busca é o projeto identificado pela key passada 
     * como parâmetro.
     * 
     * O parâmetro filter deve ser usado para indicar quais campos do 
     * objeto Media devem ser retornados no resultado. Para definir um filtro, 
     * basta passar como parâmetro uma lista de campos, separados por 
     * “ponto e vírgula”:
     * 
     * ex. filter=campo1;campo2;campo3
     * 
     * @param string $mediaId (obrigatório)
     * @param string $filter  (opcional)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediasmediaid/
     * @access public
     */
    public function getMediaById( $mediaId , $filter = array() ) 
    {
        $queryString = $this->_toQueryStr($filter);
        $url = $this->_apiUrl() .'/medias/'. $mediaId 
                    .'/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    /**
     * Retorna o conjunto de URLs correspondentes ao 
     * MediaFile identificado pelo parâmetro mediaFileId.
     * Uma objeto Media pode conter vários arquivos (MediaFile), 
     * que correspondem à diferentes versões daquela mídia. 
     * Cada MediaFile possui um mediaFileId.
     * 
     * @param string $mediaFileId (obrigatório)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediasurlsmediafileid/
     * @access public
     */
    public function getMediaUrlsByFileId( $mediaFileId ) 
    {
        $url = $this->_apiUrl() .'/medias/urls/'. $mediaFileId 
                    .'/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Adiciona uma visualização ao arquivo (MediaFile), 
     * identificado pelo mediaFileId.
     * 
     * Este método permite que seja registrada a porcentagem da mídia 
     * assistida, através do parâmetro quarter.
     * 
     * @param string $mediaFileId (obrig)
     * @param array  $options     (obrig)
     * 
     * @access public
     * @link http://docs.liquidplatform.com/2010/09/mediasviewsmediafileid/
     */
    /*public function getMediaViewsByFileId( $mediaFileId, $options ) {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/medias/views/'. $mediaFileId 
        			.'/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) 
             $url .= $queryString;
        return $this->_get($url);
    }*/
    
    /**
     * Retorna um objeto RatingSummary, que representa um resumo 
     * das informações de votos da mídia identificada pelo mediaId.
     * 
     * @param string $mediaId (obrigatório)
     * @param array  $options (opcional)
     * 
     * @return mixed objects array
     * @access public
     * @link http://docs.liquidplatform.com/2010/09/mediasmediaidrating/
     */
    public function getMediaRatingById( $mediaId, $options = array() ) 
    {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/medias/'. $mediaId 
                    .'/rating/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    /**
     * Retorna uma lista de objetos Media relacionados à mídia 
     * correspondentes ao mediaId. Ou seja, a lista de mídias 
     * relacionadas à mídia em questão.
     * 
     * Esta lista é ordenada por ordem de relevância.
     * 
     * @param string $mediaId (obrigatório)
     * @param array  $options (opcional)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediasmediaidrelated/
     * @access public
     */
    public function getMediaRelatedById( $mediaId, $options = array() ) 
    {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/medias/'. $mediaId 
                    .'/related/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    /**
     * Retorna uma lista com os thumbnails da mídia 
     * identificada pelo mediaId.
     * Observação: Não assuma qualquer tipo de ordenação da lista se não 
     * for usado ou não existir um parâmetro para ordenar o resultado.
     * 
     * @param string $mediaId (obrigatório)
     * @param array  $options (opcional)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediasmediaidthumbs/
     * @access public
     */
    public function getMediaThumbsById( $mediaId, $options = '' ) 
    {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/medias/'. $mediaId 
                    .'/thumbs/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    /**
     * Retorna o número de visualizações da mídia identificada pelo mediaId.
     * 
     * Este valor é a soma dos views de todas as versões 
     * (MediaFiles) desta mídia. Caso não existam views, 
     * é retornado um SimpleResult com o valor 0.
     * 
     * @param string $mediaId (obrigatório)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediasmediaidviews/
     * @access public
     */
    public function getMediaViewsById( $mediaId ) 
    {
        $url = $this->_apiUrl() .'/medias/'. $mediaId 
                    .'/views/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna o conjunto de URLs, na mídia identificada pelo 
     * mediaId, correspondentes ao parâmetro outputName.
     * 
     * Uma objeto Media pode conter vários arquivos (MediaFile), 
     * que correspondem à diferentes versões daquela mídia. 
     * Cada MediaFile é identificado por um outputName.
     * 
     * @param string $mediaId    (obrigatório)
     * @param string $outputName (obrigatório)
     * 
     * @return mixed objects array
     * @link 
     * http://docs.liquidplatform.com/2010/09/mediasurlsmediaidoutputname/
     * @access public
     */
    public function getMediaUrlsByIdAndOutputName( $mediaId, $outputName ) 
    {
        $url = $this->_apiUrl() .'/medias/urls/'. $mediaId 
                   .'/'. $outputName .'/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Adiciona uma visualização ao arquivo (MediaFile), 
     * identificado pelo outputName, contido na mídia 
     * correspondente ao mediaId passado como parâmetro.
     * 
     * Este método permite que seja registrada a porcentagem 
     * da mídia assistida, através do parâmetro quarter.
     * 
     * @param string $mediaId    (obrigatório)
     * @param string $outputName (obrigatório)
     * @param array  $options    (obrigatório)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/mediasmediaidviewsoutputname/
     * @access public
     */
    public function getMediaViewsByIdAndOutputName( $mediaId, $outputName, 
        $options 
    ) {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/medias/'. $mediaId 
                   .'/views/'. $outputName .'/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    
    /**
     * Retorna uma lista de objetos Channel, que representa 
     * todos os canais cadastrados no projeto identificado 
     * pelo parâmetro key.
     * 
     * @param array $options (opcional)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/channels/
     * @access public
     */
    public function channels( $options = array() )
    {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/channels/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }
    
    /**
     * Retorna o número de canais no projeto correspondente à API 
     * key passada como parâmetro. Caso não existam canais, 
     * é retornado um SimpleResult com o valor 0.
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/channelscount/
     * @access public
     */
    public function channelsCount()
    {
        $url = $this->_apiUrl() .'/channels/count/?key=' . $this->_api_key;
        return $this->_get($url);
    }


    /**
     * Retorna o objeto Channel correspondente ao parâmetro channelId. 
     * O escopo da busca é o projeto identificado 
     * pela key passada como parâmetro.
     * O parâmetro filter deve ser usado para indicar quais campos 
     * do objeto Channel devem ser retornados no resultado. 
     * Para definir um filtro, basta passar como parâmetro uma 
     * lista de campos, separados por “ponto e vírgula”:
     *  ex:  filter=campo1;campo2;campo3
     * 
     * @param string $channelId (obrigatório)
     * @param array  $options   (opcional)
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/channelschannelid/
     * @access public
     */
    public function getChannelById( $channelId, $options = array() )
    {
        $queryString = $this->_toQueryStr($options);
        $url = $this->_apiUrl() .'/channels/'. $channelId 
                    .'/?key=' . $this->_api_key;
        if ( ! empty($queryString) ) {
             $url .= $queryString;
        }
        return $this->_get($url);
    }

    /**
     * Lista todos os outputs cadastrados no projeto correspondente 
     * à API key passada como parâmetro.
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2010/09/outputs/
     * @access public
     */
    public function outputs()
    {
        $url = $this->_apiUrl() .'/outputs/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna total de mídias(vídeos) no projeto, numa data específica.
     * Este valor é a quantidade de vídeos até a data, ou seja, 
     * o valor acumulado.
     * Caso uma data não seja passada como parâmetro, a data atual 
     * do sistema é assumida.
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2011/05/reportdetailtraffic/
     * @access public
     */
    public function getReportDetailTraffic()
    {
        $url = $this->_apiUrl() .'/report/detail/traffic/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna total de mídias(vídeos) no projeto, numa data específica.
     * Este valor é a quantidade de vídeos até a data, ou seja, 
     * o valor acumulado.
     * Caso uma data não seja passada como parâmetro, a data atual 
     * do sistema é assumida.
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2011/05/reportdetailmedias/
     * @access public
     */
    public function getReportDetailMedias()
    {
        $url = $this->_apiUrl() .'/report/detail/medias/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna o total de views em mídias num projeto, num intervalo 
     * de data específico.
     * Este valor é a soma das views em cada dia dentro do intervalo passado,
     * inclusive as datas passadas.
     * Caso as datas não sejam passadas como parâmetro, o intervalo de 
     * datas assume o valor default de um mês.
     * A data final é assumida como a data atual do sistema e a data de 
     * início assume o valor, menos um mês.
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2011/05/reportdetailviews/
     * @access public
     */
    public function getReportDetailViews()
    {
        $url = $this->_apiUrl() .'/report/detail/views/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna total de storage no projeto, em bytes*, numa data específica.
     * Este valor é a quantidade armazenada até a data, ou seja, o valor 
     * acumulado.
     * Caso uma data não seja passada como parâmetro, a data atual do 
     * sistema é assumida.
     * 
     * @return mixed objects array
     * @link http://docs.liquidplatform.com/2011/05/reportdetailstorage/
     * @access public
     */
    public function getReportDetailStorage()
    {
        $url = $this->_apiUrl() .'/report/detail/storage/?key=' . $this->_api_key;
        return $this->_get($url);
    }

    /**
     * Retorna a url já com a versão da api
     * 
     * @return string $url
     * @access private
     */
    private function _apiUrl()
    {
        return self::API_DOMAIN . '/'. self::API_VERSION;
    }

    /**
     * Converte os parâmetros de 
     * array para query string
     * 
     * @param array $options (obrigatório)
     * 
     * @return string $queryStr
     * @access private
     */
    private function _toQueryStr( array $options )
    {
        $queryStr = '';
        if ( ! empty($options) and is_array($options) ) {
            foreach ( $options as $key => $value ) {
                $queryStr .= '&' . $key . '=' . $value;
            }
        }
        return $queryStr;
    }
    
    /**
     * Guarda algum valor em memória
     * 
     * @param string $queryStr (obrigatório)
     * @param mixed  $value    (obrigatório)
     * 
     * @return true
     * @access private
     */
    private function _setQueryCache( $queryStr, $value )
    {
        $this->_memoryCache[md5($queryStr)] = $value;
        return true;
    }
    
    /**
     * Retorna o conteúdo de uma query em memória
     * ou seja, uma consulta anterior idêntica 
     * retorna da memória
     * 
     * @param array $queryStr (obrigatório)
     * 
     * @return mixed array
     * @access private
     */
    private function _getQueryCache( $queryStr )
    {
        if ( ! isset($this->_memoryCache[md5($queryStr)]) ) {
            return false;
        }
        return $this->_memoryCache[md5($queryStr)];
    }
    
    /**
     * Faz a requisição HTTP e obtém a 
     * resposta do servidor
     * 
     * @param string $queryStr (obrigatório)
     * 
     * @return object xml or string 
     * @access private
     */
    private function _get( $queryStr )
    {
        if ( ! $xml = $this->_getQueryCache($queryStr) ) {
            $resource = curl_init($queryStr);
            curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($resource);
            curl_close($resource);
            try{
                /*** validação simples de xml***/
                if ( ! preg_match("/^\<[.*]*.*[\>]+$/", $res) ) {
                    return $res;
                }
                $xml = new SimpleXMLElement($res);
            } catch ( Exception $error ) {
                return '';
            }
            $this->_setQueryCache($queryStr, $xml);
            return $xml;
        }
        return $xml;
    }
}
