# O que há de novo?

## Opac – v1.1.1-beta (2024-06-18)
*   Melhorias na tela de configuração do Opac;
*   Ficou mais fácil habilitar e desabilitas bases para o Opac, mas verifique o alerta abaixo antes de iniciar sua atualização;
*   Possibilidade de <a href="javascript:EnviarForma('presentacion.php')">edição visual dos estilos</a>;
*   Menu <a href="javascript:EnviarForma('adm_email.php')">Setup E-mail</a>
*   Google Analytics - parâmetro <a href="javascript:EnviarForma('parametros.php')">GANALYTICS</a> no arquivo opac.def para habilitar a utilização do Google Analytics;
*   No front: Adicionadas Meta tags para SEO e Redes Sociais; 
*   No front: Adicionado o modo dark;
*   No front: Adicionada a opção de ampliação e redução de fontes;

### Alertas
*   Arquivo dbNome.def dentro de /db/opac/lang/ era utilizado apenas para definir a descrição da base de dados, portanto esta funcionalidade foi incorporada ao arquivo opac_conf/lang/bases.dat para manter o padrão de todo o ABCD. Se você está atualizando seu ABCD e Opac, por favor, <a href="javascript:EnviarForma('/central/settings/opac/databases.php')">clique aqui para gerar novamente seu arquivo bases.dat </a>.


### Correções
- Corrigido o formulário de busca avançada;
- Corrigida a função para enviar termos ao formulário de pesquisa avançada;

----
## Opac – v1.1.0-beta (2023-03-28)
### Novidades
- Inclusão do Twitter Booststrap como base para novos layouts;
- O parâmetro OpacHttp torna-se obrigatório para instalações que desejam que o Opac seja a página inicial de acesso público;
- select_record.pft foi ajustado para o Bootstrap;

----
# Descrição do OPAC
### Característica

O OPAC ABCD permite até 3 níveis de pesquisa:
*   Meta pesquisa
*   Pesquise em um banco de dados específico
*   Pesquise em um subconjunto de registros em um banco de dados (tipo de material ou outra classificação definida por um prefixo do FST)

# OPAC - Estrutura de arquivos

Os arquivos na pasta ** opac \ _conf ** são para uso geral do sistema OPAC, alguns são obrigatórios para a operação básica do sistema:

bases/opac\_conf/lang/

**Arquivos necessários:**

*   bases.dat
*   lang.tab
*   footer.info
*   menu.info
*   side\_bar.info
*   sitio.info

## Formulário de Pesquisa Geral (** Metasearch **)

Os arquivos de pesquisa avançados e gratuitos precisam seguir o padrão das pesquisas gratuitas e avançadas dos bancos de dados, ou seja, se o prefixo TW \ _ for definido em um banco de dados para a pesquisa gratuita, o mesmo prefixo deve ser usado para a pesquisa geral.

*   libre.tab
*   avanzada.tab

** Os arquivos que estão no processo de avaliação no desenvolvimento. **

*   camposbusqueda.tab
*   colecciones.tab
*   destacadas.tab
*   facetas.dat
*   formatos.dat
*   autoridades\_opac.pft
*   indice.ix
*   opac.pft
*   opac\_loanobjects.pft
*   select\_record.pft

## Configurando um banco de dados no OPAC

Os arquivos de configuração para um banco de dados habilitados para serem exibidos no OPAC devem estar presentes junto com o banco de dados em uma pasta chamada OPAC/LANG: **/bases/dbName/opac/lang/**

*   dbName.def
*   dbName.ix
*   dbName.lang
*   dbName\_avanzada.tab
*   dbName\_avanzada_col.tab
*   dbName\_facetas.dat
*   dbName\_formatos.dat
*   dbName\_libre.tab

### Pesquise por tipos de registro (dbName\_colecciones.tab)

*   dbName\_colecciones.tab

### Pesquisa avançada por tipos de registro(dbName\_colecciones.tab)

Files to search by collection type, where the \_\[letter\] suffix is related to the first column of the dbName\_collections.tab file

*   dbName\_avanzada\_\[letter\].tab


### Use a variável $ barra lateral para mostrar ou ocultar a barra lateral:

$sidebar=N // hide the bar
$sidebar=Y // shows the sidebar
