<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Job;
use App\Models\Draft;
use App\Models\Link;
use App\Models\Article;
use App\Models\SocialMediaJob;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LinkController extends Controller
{
    public function checkLink()
    {
        $url = 'https://angoemprego.com/vagas/desenvolvedor-de-front-end-2/'; // Recebe o link da requisição

        // Faça a requisição HTTP para o link
        $client = new Client();
        $response = $client->get($url);
        $html = $response->getBody()->getContents();

        // Use uma expressão regular para encontrar a tag script com "@type":"JobPosting"
        $pattern = '/<script[^>]*>\s*{[^<]*"@type":"JobPosting"[^<]*}\s*<\/script>/s';

        if (preg_match($pattern, $html, $matches)) {
            // Encontrou um objeto JSON com "@type":"JobPosting"
            $jsonString = $matches[0];

            // Remova a parte do script e mantenha apenas o JSON
            $jsonString = preg_replace('/<script[^>]*>|<\/script>/', '', $jsonString);

            $jsonObject = json_decode($jsonString);

            if ($jsonObject !== null) {
                return response()->json(['message' => 'Objeto JSON encontrado', 'data'  => $jsonObject]);
            }
        }

        // Não encontrou um objeto JSON com "@type":"JobPosting"
        return response()->json(['message' => 'Objeto JSON não encontrado']);
    }

    public function scrapeData()
    {
        // Crie uma instância do cliente Guzzle
        $client = new Client();

        // Faça a requisição HTTP para o URL desejado
        $url = 'https://angoemprego.com/vagas/desenvolvedor-de-front-end-2/'; // Substitua pelo URL real
        $response = $client->get($url);

        // Obtenha o conteúdo da resposta
        $html = $response->getBody()->getContents();

        // Use técnicas de manipulação de string para extrair o valor do atributo href
        $startMarker = '<div class="application_details">';
        $endMarker = '</div>';

        $startPos = strpos($html, $startMarker);

        if ($startPos !== false) {
            $endPos = strpos($html, $endMarker, $startPos);

            if ($endPos !== false) {
                $length = $endPos - $startPos;
                $data = substr($html, $startPos, $length);

                // Use uma expressão regular para extrair o valor do atributo href
                preg_match('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i', $data, $matches);

                if (isset($matches[1])) {
                    $href = $matches[1];
                    return response()->json(['href' => $href]);
                }
            }
        }

        return response()->json(['message' => 'Href não encontrado']);
    }

    public function scrapeData2()
    {
        // Crie uma instância do cliente Guzzle
        $client = new Client();

        // Faça a requisição HTTP para o URL desejado
        $url = 'https://sovagas.co.mz/'; // Substitua pelo URL real
        $response = $client->get($url);

        // Obtenha o conteúdo da resposta
        $html = $response->getBody()->getContents();

        // Use o Symfony DomCrawler para analisar o HTML
        $crawler = new Crawler($html);

        // Encontre todas as âncoras <a> com a classe .job-listing
        $jobListings = $crawler->filter('.job_listings .job_listing a');

        // Inicialize um array para armazenar os resultados
        $results = [];

        // Itere sobre cada âncora e obtenha o valor do atributo href
        $jobListings->each(function ($node) use (&$results) {
            $href = $node->attr('href');
            $results[] = $href;
        });

        return response()->json(['job_listings' => $results]);
    }



    public function MyScrap(){

        $url = 'https://angoemprego.com/vagas/tecnico-senior-de-manutencao-de-geradores/'; // Recebe o link da requisição

        // Faça a requisição HTTP para o link
        $client = new Client();
        $response = $client->get($url);
        $html = $response->getBody()->getContents();

        // Use uma expressão regular para encontrar a tag script com "@type":"JobPosting"
        $pattern = '/<script[^>]*>\s*{[^<]*"@type":"JobPosting"[^<]*}\s*<\/script>/s';

        //Variavel para receber o Json
        $Resultado = null;

        if (preg_match($pattern, $html, $matches)) {
            // Encontrou um objeto JSON com "@type":"JobPosting"
            $jsonString = $matches[0];

            // Remova a parte do script e mantenha apenas o JSON
            $jsonString = preg_replace('/<script[^>]*>|<\/script>/', '', $jsonString);

            $jsonObject = json_decode($jsonString);

            if ($jsonObject !== null) {
                $Resultado = $jsonObject;
            }
        }

        // Verifique se a data de criação é igual à data atual
        if (date('Y-m-d') == date('Y-m-d', strtotime($Resultado->datePosted))) {
            $filteredResult = ['url' => $url, 'datePosted' => $Resultado->datePosted];
        } else {
            $filteredResult = null;
        }
        return response()->json(['filtered_result' => $filteredResult]);
    }



    //******** OUTROS TESTES   */

    function removeInlineStyles($html) {
        // Expressão regular para encontrar estilos inline
        $pattern = '/(<[^>]+) style=".*?"/i';

        // Substituir estilos inline vazios
        $cleanHtml = preg_replace($pattern, '$1', $html);

        return $cleanHtml;
    }


    public function createByLink()
    {
        $url = 'https://angoemprego.com/vagas/tecnico-senior-de-manutencao-de-geradores/'; // Recebe o link da requisição

        // Faça a requisição HTTP para o link
        $client = new Client();
        $response = $client->get($url);
        $html = $response->getBody()->getContents();

        // Use uma expressão regular para encontrar a tag script com "@type":"JobPosting"
        $pattern = '/<script[^>]*>\s*{[^<]*"@type":"JobPosting"[^<]*}\s*<\/script>/s';

        //Variavel para receber o Json
        $Resultado = null;

        if (preg_match($pattern, $html, $matches)) {
            // Encontrou um objeto JSON com "@type":"JobPosting"
            $jsonString = $matches[0];

            // Remova a parte do script e mantenha apenas o JSON
            $jsonString = preg_replace('/<script[^>]*>|<\/script>/', '', $jsonString);

            $jsonObject = json_decode($jsonString);

            if ($jsonObject !== null) {
                $Resultado = $jsonObject;
            }
        }

        // Crie um novo registro usando o modelo
        $newRecord = Job::create([
            'title' => $Resultado->title,
            'company' => $Resultado->hiringOrganization->name,
            'province' => $Resultado->jobLocation->address,
            'description' => preg_replace('/(<[^>]+) style=".*?"/i', '$1', html_entity_decode($Resultado->description)),
            'email_or_link' => '...',
            'photo' => 'images/jobs/default.jpg', // Caminho da imagem armazenado no banco de dados
            'country_id' => 2,
        ]);

        return response()->json(['record' => $newRecord]);
    }

    public function getLinks(){
         // Crie uma instância do cliente Guzzle
         $client = new Client();

         // Faça a requisição HTTP para o URL desejado
         $url = 'https://sovagas.co.mz/'; // Substitua pelo URL real
         $response = $client->get($url);

         // Obtenha o conteúdo da resposta
         $html = $response->getBody()->getContents();

         // Use o Symfony DomCrawler para analisar o HTML
         $crawler = new Crawler($html);

         // Encontre todas as âncoras <a> com a classe .job-listing
         $jobListings = $crawler->filter('.job_listing a');

         // Inicialize um array para armazenar os resultados
         $results = [];

         // Itere sobre cada âncora e obtenha o valor do atributo href
         $jobListings->each(function ($node) use (&$results) {
             $href = $node->attr('href');
             $results[] = $href;
         });

         //sleep(30);
         foreach ($results as $result) {
            try {
                // Verifica se o link já existe, se sim salte para a proxima iteração
				if(Link::where('url', $result)->exists()){
					continue;
				}

                // Faça a requisição HTTP para o link
                $client = new Client();
                $response = $client->get($result);
                $html = $response->getBody()->getContents();

                // Use uma expressão regular para encontrar a tag script com "@type":"JobPosting"
                $pattern = '/<script[^>]*>\s*{[^<]*"@type":"JobPosting"[^<]*}\s*<\/script>/s';

                //Variavel para receber o Json
                $Resultado = null;

                if (preg_match($pattern, $html, $matches)) {
                    // Encontrou um objeto JSON com "@type":"JobPosting"
                    $jsonString = $matches[0];

                    // Remova a parte do script e mantenha apenas o JSON
                    $jsonString = preg_replace('/<script[^>]*>|<\/script>/', '', $jsonString);

                    $jsonObject = json_decode($jsonString);

                    if ($jsonObject !== null) {
                        $Resultado = $jsonObject;

                        // Verifique se a data de criação é igual à data atual
                        if (date('Y-m-d') == date('Y-m-d', strtotime($Resultado->datePosted))) {
							// Crie um novo registro usando o modelo
                            $newRecord = Link::create([
                                'url' => $result,
								'country_id' => 3
                            ]);		
                        }
                    }
                }


            } catch (\Throwable $th) {
                //return response()->json(['status' => 'error']);
            }
         }

         return response()->json(['job_listings' => $results]);
    }

    public function insertJobs(){
        $jobLinks = Link::where('published', '=', false)->where('country_id', '=', 3)->get();

        foreach($jobLinks as $jobLink){
            try {

                // Faça a requisição HTTP para o link
                $client = new Client();
                $response = $client->get($jobLink->url);
                $html = $response->getBody()->getContents();

                // Use uma expressão regular para encontrar a tag script com "@type":"JobPosting"
                $pattern = '/<script[^>]*>\s*{[^<]*"@type":"JobPosting"[^<]*}\s*<\/script>/s';

                //Variavel para receber o Json
                $Resultado = null;

                if (preg_match($pattern, $html, $matches)) {
                    // Encontrou um objeto JSON com "@type":"JobPosting"
                    $jsonString = $matches[0];

                    // Remova a parte do script e mantenha apenas o JSON
                    $jsonString = preg_replace('/<script[^>]*>|<\/script>/', '', $jsonString);

                    $jsonObject = json_decode($jsonString);

                    if ($jsonObject !== null) {
                        $Resultado = $jsonObject;
                    }
                }

                // Crie um novo registro usando o modelo
                $descricao = preg_replace('/(<[^>]+) style=".*?"/i', '$1', html_entity_decode($Resultado->description));
                $descricaoTratada = explode('<p class="job_tags">', $descricao);
                $MinhaMarca = '<h2>------------------<o:p></o:p></h2><h2><strong><span style="color: #212529; font-weight: normal;">Empregos Yoyota - Aqui voc&ecirc; encontra o seu emprego ideal.</span></strong><o:p></o:p></h2><p style="margin-top: 0cm; background: white;"><span style="color: #212529;">Encontre aqui as melhores vagas de emprego para 2023, oportunidades de recrutamento em Mo&ccedil;ambique dispon&iacute;veis no nosso portal para candidaturas. Tamb&eacute;m informamos sobre concurso p&uacute;blico para 2023 e muito mais.<o:p></o:p></span></p><p style="margin-top: 0cm; background: white;"><strong><span style="color: #212529;">Tags:</span></strong><span style="color: #212529;">&nbsp;emprego em Mo&ccedil;ambique, concurso publico Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego 2023, vagas de emprego em Mo&ccedil;ambique 2023, vagas de emprego em Mo&ccedil;ambique 2022, minsa, governo provincial, resultados, gpl, recrutamento 2023, 2024, 2022, concurso p&uacute;blico moz emprego mmo Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego 2023 Concurso P&uacute;blico 2023 Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Mo&ccedil;ambique Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Concurso P&uacute;blico Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Concurso P&uacute;blico 2023 Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Recrutamento 2023 Moz Emprego Moz Emprego 2023 MozEmprego2023 Concurso P&uacute;blico 2023 Concurso P&uacute;blico De Mo&ccedil;ambique Concurso P&uacute;blico Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Concurso P&uacute;blico Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego 2023 Concurso P&uacute;blicos Em Mo&ccedil;ambique 2023 Recrutamento Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Recrutamento Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego 2023 Trabalhar Na Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego concurso p&uacute;blico Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego candidatura concurso p&uacute;blico 2023 em Mo&ccedil;ambique Concurso &aacute;gil minfin gov ao Concurso p&uacute;blico na Emprego em Moz Vagas Job 2023 est&aacute;gio empregos yoyota est&aacute;gio vagas de emprego 2023<o:p></o:p></span></p><p style="margin-top: 0cm; background: white;"><strong><span style="color: #212529;">N&atilde;o recrutamos ningu&eacute;m, a nossa miss&atilde;o &eacute; informar as vagas de emprego de fontes cred&iacute;veis</span></strong><span style="color: #212529;"><o:p></o:p></span></p><p style="margin-top: 0cm; background: white;"><strong><span style="color: #212529;">Entre no nosso grupo do WhatsApp&nbsp;</span></strong><a href="https://chat.whatsapp.com/BLXhPWYKjQW4th1arYBvuY"><strong>https://chat.whatsapp.com/BLXhPWYKjQW4th1arYBvuY</strong></a><span style="color: #212529;"><o:p></o:p></span></p>';
                $newRecord = Job::create([
                    'title' => $Resultado->title,
                    'company' => $Resultado->hiringOrganization->name,
                    'province' => $Resultado->jobLocation->address,
                    'description' => $descricaoTratada[0] . $MinhaMarca,
                    'email_or_link' => '...',
                    'photo' => 'images/jobs/default.jpg', // Caminho da imagem armazenado no banco de dados
                    'country_id' => 3,
                ]);
				
				//*********************Postar no Facebook*****************************************
                //Inicia novo Client
                $clientParaApi = new Client();
                // URL da API do Facebook
                $apiUrl = 'https://graph.facebook.com/v18.0/me/feed';
                // Parâmetros da solicitação POST
                $params = [
                    'form_params' => [
                        'message' => $newRecord->title . "\n.\nSe você deseja saber mais sobre a oportunidade clique no link: https://moz.empregosyoyota.net/empregos/" . $newRecord->slug . "\n.",
                        'link' => 'https://moz.empregosyoyota.net/empregos/' . $newRecord->slug,
                        'access_token' => env('FACEBOOK_ACCESS_TOKEN_1'),
                    ],
                ];
                // Realize a solicitação POST
                $response = $clientParaApi->post($apiUrl, $params);
                //*********************************************************************************** */
				
            } catch (\Throwable $th) {
                //throw $th;
            }

            //colocar como publicado
            $jobLink->published = true;
            $jobLink->save();

        }

        return response()->json(['record' => $jobLinks]);
    }
	
	
	
	//**************************** PARA ANGOLA
	
	
	
	//Insere dados no Drafts de Angola
	public function insertJobsAngola(){
        // Crie uma instância do cliente Guzzle
		$client = new Client();

		// Faça uma requisição GET para a URL desejada
		$response = $client->get('https://angoemprego.com/wp-json/wp/v2/job-listings');

		$Empregos = [];

		// Verifique se a requisição foi bem-sucedida (código de status 200)
		if ($response->getStatusCode() === 200) {
			// Obtenha o conteúdo da resposta em formato JSON
			$json = $response->getBody()->getContents();

			// Decodifique o JSON para um array ou objeto PHP
			$data = json_decode($json);

			if (!empty($data)) {
				$Empregos = $data;
			}
		}

		foreach ($Empregos as $emprego) {

            // Verifica se o link já existe ou se a data do post é de hoje, se sim para um dos dois salte para a proxima iteração
            if(Link::where('url', $emprego->link)->exists() || !(date('Y-m-d') == date('Y-m-d', strtotime($emprego->date))))
            {
                continue;
            }

            //Ignorar vagas de AngoEmpregoPro
            if (strpos($emprego->meta->_application, "empregopro.ao") !== false) {
				continue;
			}

            //Ignorar vagas de Jobartis
            if (strpos($emprego->meta->_application, "jobartis.com") !== false) {
				continue;
			}

            // Trate a descrição
			$descricao = preg_replace('/(<[^>]+) style=".*?"/i', '$1', html_entity_decode($emprego->content->rendered));
			$descricaoTratada = explode('Se você tem interesse nesta o', $descricao);
			$MinhaMarca = '<h2>-------------</h2><h2>Empregos Yoyota - Aqui você encontra o seu emprego ideal.</h2><p>Encontre aqui as melhores vagas de emprego para 2024, oportunidades de recrutamento em Angola disponíveis no nosso portal para candidaturas. Também informamos sobre concurso público para 2024 e muito mais.<br /><strong>Tags:</strong>&nbsp;emprego em angola, concurso publico agt 2023, vagas de emprego em angola 2024, vagas de emprego em angola 2022, minsa, governo provincial, resultados, gpl, recrutamento 2023, 2024, 2025, concurso público agt 2024</p><h2>Não recrutamos ninguém, a nossa missão é informar as vagas de emprego publicadas no Jornal de Angola e de outras fontes credíveis.</h2><h2>Faça Curso Básico de Excel DE GRAÇA clicando no link&nbsp;<a href="https://aocursos.com/courses/46">https://aocursos.com/courses/46</a></h2>';

			// Crie um texto de candidatura
			$TextoCandidatura = "";

			if (strpos($emprego->meta->_application, "@") !== false) {
				$TextoCandidatura = '<h1>CANDIDATURAS</h1><p>Faça a sua candidatura através do e-mail:&nbsp;<a href="mailto:' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			} elseif ($emprego->meta->_application !== "") {
				$TextoCandidatura = '<h1>CANDIDATURAS</h1><p>Faça a sua candidatura através do link:&nbsp;<a href="' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			}

			// Crie um novo registro usando o modelo
			Draft::create([
				'title' => 'Vaga para ' . $emprego->title->rendered,
				'company' => $emprego->meta->_company_name,
				'province' => $emprego->meta->_job_location,
				'description' => $descricaoTratada[0] . $TextoCandidatura . $MinhaMarca,
				'email_or_link' => $emprego->meta->_application,
				'photo' => 'images/jobs/default.jpg', // Caminho da imagem armazenado no banco de dados
				'country_id' => 1,
			]);

            //Adicionar novo Registro na tabela Link
            Link::create([
                'url' => $emprego->link,
                'country_id' => 1,
				'published' => true
            ]);
		}
    }

    public function PublicarRascunho(){
        $drafts = Draft::get();

        foreach($drafts as $draft){
            $newRecord = Job::create([
                'title' => $draft->title,
                'company' => $draft->company,
                'province' => $draft->province,
                'description' => $draft->description,
                'email_or_link' => $draft->email_or_link,
                'photo' => $draft->photo,
                'country_id' => $draft->country_id,
            ]);
			
			$draft->delete();
        }
    }
	
	public function PublicarVagasDoDia()
    {
        $EmpregosHoje = Job::whereDate('created_at', Carbon::now())->where('country_id','=',1)->get();
        $EmpregosOntem = Job::whereDate('created_at', Carbon::now()->subDay())->where('country_id','=',1)->get();
        $EmpregosAntesOntem = Job::whereDate('created_at', Carbon::now()->subDays(2))->where('country_id','=',1)->get();

        $ListaHoje = '';
		$ListaParaFacebook = '';
        foreach($EmpregosHoje as $EmpregoHoje){
            $ListaHoje = $ListaHoje . '<h1><span><a href="https://ao.empregosyoyota.net/empregos/' . $EmpregoHoje->slug . '">---- ' . $EmpregoHoje->title . ' ----</a></span></h1>' . $EmpregoHoje->description;
			$ListaParaFacebook = $ListaParaFacebook . $EmpregoHoje->title . "\nCandidatura: " . $EmpregoHoje->email_or_link . "\n--------\n";
        }
        if($EmpregosHoje->count() > 0){
            $ListaHoje = '<h1>Confira agora as vagas do dia ' . Carbon::now()->format('d-m-Y') . '</h1>' . $ListaHoje;
        }

        $ListaOntem = '';
        foreach($EmpregosOntem as $EmpregoOntem){
            $ListaOntem = $ListaOntem . '<p><span><a href="https://ao.empregosyoyota.net/empregos/' . $EmpregoOntem->slug . '">' . $EmpregoOntem->title . ' (Clique aqui)</a></span></p>';
        }
        if($EmpregosOntem->count() > 0){
            $ListaOntem = '<h1>Vagas do dia ' . Carbon::now()->subDay()->format('d-m-Y') . '</h1>' . $ListaOntem;
        }

        $ListaAntesOntem = '';
        foreach($EmpregosAntesOntem as $EmpregoAntesOntem){
            $ListaAntesOntem = $ListaAntesOntem . '<p><span><a href="https://ao.empregosyoyota.net/empregos/' . $EmpregoAntesOntem->slug . '">' . $EmpregoAntesOntem->title . ' (Clique aqui)</a></span></p>';
        }
        if($EmpregosAntesOntem->count() > 0){
            $ListaAntesOntem = '<h1>Vagas do dia ' . Carbon::now()->subDays(2)->format('d-m-Y') . '</h1>' . $ListaAntesOntem;
        }

        if($EmpregosHoje->count() > 3){
            $MinhaMarca = '<h2>-------------</h2><h2>Empregos Yoyota - Aqui voc&ecirc; encontra o seu emprego ideal.</h2><p>Encontre aqui as melhores vagas de emprego para 2023, oportunidades de recrutamento em Angola dispon&iacute;veis no nosso portal para candidaturas. Tamb&eacute;m informamos sobre concurso p&uacute;blico para 2023 e muito mais.<br /><strong>Tags:</strong>&nbsp;emprego em angola, concurso publico agt 2023, vagas de emprego em angola 2023, vagas de emprego em angola 2022, minsa, governo provincial, resultados, gpl, recrutamento 2023, 2024, 2022, concurso p&uacute;blico agt 2023</p><h2>N&atilde;o recrutamos ningu&eacute;m, a nossa miss&atilde;o &eacute; informar as vagas de emprego&nbsp;publicadas no Jornal de Angola e&nbsp;de outras fontes cred&iacute;veis.</h2><h2>Baixe modelos de curr&iacute;culos clicando no link&nbsp;<a href="https://ao.empregosyoyota.net/modelos-de-curriculos">https://ao.empregosyoyota.net/modelos-de-curriculos</a></h2>';
            $PostCompleto = '<p><span>Hoje, estamos empolgados em compartilhar as vagas de emprego do dia de hoje com voc&ecirc;. Se voc&ecirc; est&aacute; em busca de uma nova carreira, um desafio emocionante ou simplesmente quer expandir seus horizontes profissionais, confira nossas vagas do dia!</span></p><p><span>&nbsp;</span></p><p><strong><span>Observa&ccedil;&atilde;o 1:</span></strong><span> Todas as vagas s&atilde;o gratu&iacute;tas e n&atilde;o implicam qualquer custo.</span></p><p><strong><span>Observa&ccedil;&atilde;o 2:</span></strong><span> N&atilde;o recrutamos ningu&eacute;m, a nossa miss&atilde;o &eacute; informar as vagas de emprego publicadas no Jornal de Angola e de outras fontes cred&iacute;veis.</span></p>';
            $PostCompleto = $PostCompleto . $ListaHoje . $ListaOntem . $ListaAntesOntem . $MinhaMarca;
            
			$TituloPost = 'Confira agora as vagas do dia ' . Carbon::now()->format('d-m-Y');
			
			//$TituloPost = $this->TituloViaGemini();
			
            $newRecord = Article::create([
                'title' => $TituloPost,
                'description' => $PostCompleto,
                'photo' => 'images/articles/default.jpg',
                'country_id' => 1
            ]);

            //Tratar Texto do Post para Facebook e Linkedin
            $PostTratado = $newRecord->title . "\n.\n" . $ListaParaFacebook . "\n.\nSe você deseja saber mais clique no link: https://ao.empregosyoyota.net/articles/" . $newRecord->slug . "\n.";
            
            //*********************Postar no Facebook*****************************************
            //Inicia novo Client
            $clientParaApi = new Client();
            // URL da API do Facebook
            $apiUrl = 'https://graph.facebook.com/v18.0/me/feed';
            // Parâmetros da solicitação POST
            $params = [
                'form_params' => [
                    'message' => $PostTratado,
                    'link' => 'https://ao.empregosyoyota.net/articles/' . $newRecord->slug,
                    'access_token' => env('FACEBOOK_ACCESS_TOKEN_2'),
                ],
            ];
            // Realize a solicitação POST
            $response = $clientParaApi->post($apiUrl, $params);
            //*********************************************************************************** */
            
             /*AGORA VAMOS POSTAR NO LINKEDIN*/
        	$link = "https://ao.empregosyoyota.net/articles/" . $newRecord->slug;
        	$linkImage = "https://ao.empregosyoyota.net/storage/" . $newRecord->photo;
        	$this->PublicarLinkedIn2($PostTratado, $link, $linkImage);
        	
        	//*******************************

        }
    }
	
	public function ObterDrafts()
	{
		// Crie uma instância do cliente Guzzle
		$client = new Client();

		// Faça uma requisição GET para a URL desejada
		$response = $client->get('https://angorecruta.com/wp-json/wp/v2/job-listings');

		$Empregos = [];

		// Verifique se a requisição foi bem-sucedida (código de status 200)
		if ($response->getStatusCode() === 200) {
			// Obtenha o conteúdo da resposta em formato JSON
			$json = $response->getBody()->getContents();

			// Decodifique o JSON para um array ou objeto PHP
			$data = json_decode($json);

			if (!empty($data)) {
				$Empregos = $data;
			}
		}

		foreach ($Empregos as $emprego) {
			// Trate a descrição
			$descricao = preg_replace('/(<[^>]+) style=".*?"/i', '$1', html_entity_decode($emprego->content->rendered));
			$descricaoTratada = explode('Se você tem interesse nesta o', $descricao);
			$MinhaMarca = '<h2>-------------</h2><h2>Empregos Yoyota - Aqui você encontra o seu emprego ideal.</h2><p>Encontre aqui as melhores vagas de emprego para 2024, oportunidades de recrutamento em Angola disponíveis no nosso portal para candidaturas. Também informamos sobre concurso público para 2024 e muito mais.<br /><strong>Tags:</strong>&nbsp;emprego em angola, concurso publico agt 2023, vagas de emprego em angola 2024, vagas de emprego em angola 2022, minsa, governo provincial, resultados, gpl, recrutamento 2023, 2024, 2025, concurso público agt 2024</p><h2>Não recrutamos ninguém, a nossa missão é informar as vagas de emprego publicadas no Jornal de Angola e de outras fontes credíveis.</h2><h2>Baixe modelos de currículos clicando no link&nbsp;<a href="https://ao.empregosyoyota.net/modelos-de-curriculos">https://ao.empregosyoyota.net/modelos-de-curriculos</a></h2>';

			// Crie um texto de candidatura
			$TextoCandidatura = "";

			if (strpos($emprego->meta->_application, "@") !== false) {
				$TextoCandidatura = '<h1>CANDIDATURAS</h1><p>Faça a sua candidatura através do e-mail:&nbsp;<a href="mailto:' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			} elseif ($emprego->meta->_application !== "") {
				$TextoCandidatura = '<h1>CANDIDATURAS</h1><p>Faça a sua candidatura através do link:&nbsp;<a href="' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			}

			// Crie um novo registro usando o modelo
			Draft::create([
				'title' => 'Vaga para ' . $emprego->title->rendered,
				'company' => $emprego->meta->_company_name,
				'province' => $emprego->meta->_job_location,
				'description' => $descricaoTratada[0] . $TextoCandidatura . $MinhaMarca,
				'email_or_link' => $emprego->meta->_application,
				'photo' => 'images/jobs/default.jpg', // Caminho da imagem armazenado no banco de dados
				'country_id' => 1,
			]);
		}
	}
	
	public function ObterAngolaEmpregoAngoEmprego($website = 'angoemprego.com')
	{
		// Crie uma instância do cliente Guzzle
		$client = new Client();

		// Faça uma requisição GET para a URL desejada
		$response = $client->request('GET', 'https://' . $website . '/wp-json/wp/v2/job-listings', ['verify' => false]);

		$Empregos = [];

		// Verifique se a requisição foi bem-sucedida (código de status 200)
		if ($response->getStatusCode() === 200) {
			// Obtenha o conteúdo da resposta em formato JSON
			$json = $response->getBody()->getContents();
            
			// Decodifique o JSON para um array ou objeto PHP
			$data = json_decode($json);

			if (!empty($data)) {
				$Empregos = $data;
			}
		}
		
		foreach ($Empregos as $emprego) {

            // Verifica se o link já existe, se sim salte para a proxima iteração
            if(Link::where('url', $emprego->link)->exists()) // || !(date('Y-m-d') == date('Y-m-d', strtotime($emprego->date)))
            {
                continue;
            }

            //Ignorar vagas de AngoEmprego Pro
            if (strpos($emprego->meta->_application, "empregopro.ao") !== false) {
				continue;
			}

            //Ignorar vagas de Jobartis
            if (strpos($emprego->meta->_application, "jobartis.com") !== false) {
				//continue;
			}

            //Ignorar vagas de Linkedin
            if (strpos($emprego->meta->_application, "linkedin.com") !== false) {
				//continue;
			}

            //Ignorar vagas de Links
            if (strpos($emprego->meta->_application, "@") == false) {
                //continue;
            }

            // Trate a descrição
            $ExplodeText = explode('Se você tem interesse nesta oportunidade de emprego', $emprego->content->rendered)[0];
            $ExplodeText = explode('Como se Candidatar:', $ExplodeText)[0];
            $ExplodeText = explode('<a href=\"https://angoemprego.com/', $ExplodeText)[0];
			$descricao = $this->DescricaoVagaViaGemini($ExplodeText);//preg_replace('/(<[^>]+) style=".*?"/i', '$1', html_entity_decode($emprego->content->rendered));
			//echo($descricao);
            $descricaoTratada = $descricao;
			$MinhaMarca = '<h2>-------------</h2><h2>Empregos Yoyota - Aqui você encontra o seu emprego ideal.</h2><p>Encontre aqui as melhores vagas de emprego para 2024, oportunidades de recrutamento em Angola disponíveis no nosso portal para candidaturas. Também informamos sobre concurso público para 2024 e muito mais.<br /><strong>Tags:</strong>&nbsp;emprego em Angola, Emprego em Angola 2024, Emprego em Luanda, Recrutamento 2024, Recrutamento em Angola</p><h2>Não recrutamos ninguém, a nossa missão é informar as vagas de emprego publicadas no Jornal de Angola e de outras fontes credíveis.</h2>';

			// Crie um texto de candidatura
			$TextoCandidatura = "";

			if (strpos($emprego->meta->_application, "@") !== false) {
				$TextoCandidatura = '<h1>Passos para se inscrever:</h1><p>Faça a sua candidatura através do e-mail: <a href="mailto:' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			} elseif ($emprego->meta->_application !== "") {
				$TextoCandidatura = '<h1>Passos para se inscrever:</h1><p>Faça a sua candidatura através do link: <a href="' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			}

            //Criar Titulo com IA
            $IATitle = $emprego->title->rendered;
            $IATitle = $this->TituloVagaViaGemini($emprego->title->rendered);
            //$IAImagem = $this->ImagemVagaViaGemini($emprego->meta->_company_name);
            $IAAplication = $emprego->meta->_application;
            $Empresa = $emprego->meta->_company_name;
            
            if(!($website == 'angoemprego.com')){
			    $TextoCandidatura = '';
			    $IAAplication = $this->GetOnContent($descricaoTratada);
			}
			
			if(($emprego->meta->_company_name == '') || ($emprego->meta->_company_name == null)){
			    $Empresa = 'Empresa em Angola';
			}
			
            //Inserir emprego no site Angola Recruta
            $client = new Client();


            try{
                
                //Testes para o AngolaEmprego
                $response2 = $client->request('POST', 'https://angolaemprego.com/api/job/create', ['verify' => false,
                    'json' => [
                        'title' => $IATitle,
                        'company' => $Empresa,
                        'location' => $emprego->meta->_job_location,
                        'description' => $descricaoTratada . $TextoCandidatura,
                        'email_or_link' => $IAAplication,
                        'image' => 'images/jobs/default.png',
                    ]
                ]);
                
                $dadosEmprego = json_decode($response2->getBody()->getContents(), true);
                //echo($dadosEmprego['slug']);
    
                //Adicionar novo Registro na tabela Link
                Link::create([
                    'url' => $emprego->link,
                    'country_id' => 1
                ]);

                
                //Publicar no Linkedin e Facebook***********
                $TextoEmprego = $this->DescricaoLimpa($descricaoTratada . $TextoCandidatura);
                $TextoEmprego = $TextoEmprego . "\n.\n https://angolaemprego.com/vagas/" . $dadosEmprego['slug'];
                $LinkEmprego = "https://angolaemprego.com/vagas/" . $dadosEmprego['slug'];
                // Defina e codifique o texto
                $text = $IATitle . "\n.\n" . $TextoEmprego . "\n.\nMais Vagas em: https://angolaemprego.com/vagas/";
                
                //Facebook
                $this->PublicarFacebook2($IATitle . "\n.\n" . $TextoEmprego, $LinkEmprego, env('FACEBOOK_ACCESS_TOKEN_3'));
                
                //Linkedin
                $this->PublicarLinkedIn2($text, "https://angolaemprego.com/vagas/", "https://angolaemprego.com/storage/images/jobs/default.png", "99975145");
                
            } 
            catch(Exception $ex){}
            

		}
	}
	
	public function ObterEmpregosYoyotaAngoEmprego()
	{
		// Crie uma instância do cliente Guzzle
		$client = new Client();

		// Faça uma requisição GET para a URL desejada
		$response = $client->request('GET', 'https://angoemprego.com/wp-json/wp/v2/job-listings', ['verify' => false]);

		$Empregos = [];

		// Verifique se a requisição foi bem-sucedida (código de status 200)
		if ($response->getStatusCode() === 200) {
			// Obtenha o conteúdo da resposta em formato JSON
			$json = $response->getBody()->getContents();
            
			// Decodifique o JSON para um array ou objeto PHP
			$data = json_decode($json);

			if (!empty($data)) {
				$Empregos = $data;
			}
		}
		
		foreach ($Empregos as $emprego) {

            // Verifica se o link já existe, se sim salte para a proxima iteração
            if(Link::where('url', $emprego->date)->exists()) // || !(date('Y-m-d') == date('Y-m-d', strtotime($emprego->date)))
            {
                continue;
            }

            //Ignorar vagas de AngoEmprego Pro
            if (strpos($emprego->meta->_application, "empregopro.ao") !== false) {
				continue;
			}

            //Ignorar vagas de Jobartis
            if (strpos($emprego->meta->_application, "jobartis.com") !== false) {
				continue;
			}

            //Ignorar vagas de Linkedin
            if (strpos($emprego->meta->_application, "linkedin.com") !== false) {
				//continue;
			}

            //Ignorar vagas de Links
            if (strpos($emprego->meta->_application, "@") == false) {
                //continue;
            }

            // Trate a descrição
            $ExplodeText = explode('Se você tem interesse nesta oportunidade de emprego', $emprego->content->rendered)[0];
            $ExplodeText = explode('Como se Candidatar:', $ExplodeText)[0];
            $ExplodeText = explode('<a href=\"https://angoemprego.com/', $ExplodeText)[0];
			$descricao = $this->DescricaoVagaViaGemini($ExplodeText);//preg_replace('/(<[^>]+) style=".*?"/i', '$1', html_entity_decode($emprego->content->rendered));
			//echo($descricao);
            $descricaoTratada = $descricao;
			$MinhaMarca = '<h2>-------------</h2><h2>Empregos Yoyota - Aqui você encontra o seu emprego ideal.</h2><p>Encontre aqui as melhores vagas de emprego para 2024, oportunidades de recrutamento em Angola disponíveis no nosso portal para candidaturas. Também informamos sobre concurso público para 2024 e muito mais.<br /><strong>Tags:</strong>&nbsp;emprego em Angola, Emprego em Angola 2024, Emprego em Luanda, Recrutamento 2024, Recrutamento em Angola</p><h2>Não recrutamos ninguém, a nossa missão é informar as vagas de emprego publicadas no Jornal de Angola e de outras fontes credíveis.</h2>';

			// Crie um texto de candidatura
			$TextoCandidatura = "";

			if (strpos($emprego->meta->_application, "@") !== false) {
				$TextoCandidatura = '<h1>CANDIDATURAS</h1><p>Faça a sua candidatura através do e-mail: <a href="mailto:' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			} elseif ($emprego->meta->_application !== "") {
				$TextoCandidatura = '<h1>CANDIDATURAS</h1><p>Faça a sua candidatura através do link: <a href="' . $emprego->meta->_application . '">' . $emprego->meta->_application .  '</a></p>';
			}

            //Criar Titulo com IA
            $IATitle = $emprego->title->rendered;
            $IATitle = $this->TituloVagaViaGemini($emprego->title->rendered);
            $IAImagem = $this->ImagemVagaViaGemini($emprego->meta->_company_name);

            //Inserir emprego no site Empregos Yoyota
            $client = new Client();


            try{
                
                $response = $client->request('POST', 'https://ao.empregosyoyota.net/api/job/create', ['verify' => false,
                    'json' => [
                        'title' => $IATitle,
                        'company' => $emprego->meta->_company_name,
                        'province' => $emprego->meta->_job_location,
                        'description' => $descricaoTratada . $TextoCandidatura . $MinhaMarca,
                        'email_or_link' => $emprego->meta->_application,
                        'photo' => 'images/jobs/' . $IAImagem, // Caminho da imagem armazenado no banco de dados
                        'country_id' => 1,
                    ]
                ]);
    
                //Adicionar novo Registro na tabela Link
                Link::create([
                    'url' => $emprego->date,
                    'country_id' => 1
                ]);
                
            } 
            catch(ex){}
            

		}
	}
	
	public function PublicarFacebook()
    {
        $smj = SocialMediaJob::where('post_status', '=', 0)->get();
        if(count($smj) > 0)
        {
			$item = $smj[0];
            $job = Job::find($item->job_id);

            if($job->country_id == 1)
            {
				//*********************Postar no Facebook*****************************************
			$NovaDescricao = str_replace("<br>", "\n<br>", $job->description);
			$NovaDescricao = str_replace("</p>", "</p>\n", $NovaDescricao);
			$NovaDescricao = str_replace("</h1>", "</h1>\n", $NovaDescricao);
			$NovaDescricao = str_replace("</h2>", "</h2>\n", $NovaDescricao);
			$NovaDescricao = str_replace("</h3>", "</h3>\n", $NovaDescricao);
			$NovaDescricao = str_replace("</li>", "</li>\n", $NovaDescricao);
			$NovaDescricao = explode('----------', $NovaDescricao)[0];
			$NovaDescricao = strip_tags($NovaDescricao);
			$NovaDescricao = str_replace("&nbsp;", "", $NovaDescricao);

                //Inicia novo Client
                $clientParaApi = new Client();
                // URL da API do Facebook
                $apiUrl = 'https://graph.facebook.com/v18.0/me/feed';
                // Parâmetros da solicitação POST
                $params = [
                    'form_params' => [
                        'message' => $job->title . "\n.\n". substr($NovaDescricao, 0, 120) ."...\n.\nLeia mais: https://ao.empregosyoyota.net/empregos/" . $job->slug . "\n.\n.\n.\n.\n.\n------------\nNosso Canal no WhatsApp: https://whatsapp.com/channel/0029VaCfSeo0bIdgKs7bIB3t\n.",
                        'link' => 'https://ao.empregosyoyota.net/empregos/' . $job->slug,
                        'access_token' => env('FACEBOOK_ACCESS_TOKEN_2'),
                    ],
                ];
                // Realize a solicitação POST
                $response = $clientParaApi->post($apiUrl, $params);
				
                //*********************************************************************************** */	
			}

            $item->post_status = 1;
            $item->save();
            
            
            // Defina e codifique o texto
            $text = $job->title . "\n.\n". substr($NovaDescricao, 0, 120) ."...\n.\nLeia mais: https://ao.empregosyoyota.net/empregos/" . $job->slug . "\n.\n.\n.\n.\n.\n------------\nNosso Canal no WhatsApp: https://whatsapp.com/channel/0029VaCfSeo0bIdgKs7bIB3t\n.";
            $text_encoded = urlencode($text);
		
		
		    /*AGORA VAMOS POSTAR NO LINKEDIN*/
        	$link = "https://ao.empregosyoyota.net/empregos/" . $job->slug;
        	$linkImage = "https://ao.empregosyoyota.net/storage/" . $job->photo;
        	$this->PublicarLinkedIn2($text, $link, $linkImage);
        	
        	//*******************************
		
		
		
			/*AGORA VAMOS POSTAR NO TELEGRAM*/
			// Defina a URL base completa
            $base_url = env('TELEGRAM_BOT_URL');
            
            // Construa a URL final
            $apiUrl = "{$base_url}&text={$text_encoded}";
        	$clientParaApi->request('GET', $apiUrl);
        	/*******************************/
	
    	
    	
    	/*AGORA VAMOS CATEGORIZAR*/
    	
    	$this->setCategories($job->id, $job->description);
    	
    	/*******************************/
    	
    	
        }
    }
    
    public function PublicarFacebook2(string $message, string $link, string $access_token)
    {
        try {
            // URL da API do Facebook
            $apiUrl = 'https://graph.facebook.com/v18.0/me/feed';
    
            // Inicializa o cliente HTTP
            $client = new Client();
    
            // Parâmetros para a requisição POST
            $params = [
                'form_params' => [
                    'message' => $message,
                    'link' => $link,
                    'access_token' => $access_token,
                ],
            ];
    
            // Envia a requisição para a API do Facebook
            $response = $client->post($apiUrl, $params);
    
            // Retorna a resposta decodificada
            return json_decode($response->getBody()->getContents(), true);
    
        } catch (\Exception $e) {
            // Em caso de erro, retorna a mensagem
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
        
    public function PublicarLinkedIn($post, $link)
    {
        // Variáveis definidas dentro do escopo da função
        $accessToken = env('LINKEDIN_ACCESS_TOKEN');
        $pageId = '71657080';

        // Instanciar o cliente HTTP
        $client = new Client();

        // Definir o conteúdo da postagem com apenas o link
        $postContent = [
            "author" => "urn:li:organization:" . $pageId,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary" => [
                        "text" => $post
                    ],
                    "shareMediaCategory" => "ARTICLE",
                    "media" => [
                        [
                            "status" => "READY",
                            "originalUrl" => $link
                        ]
                    ]
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
            ]
        ];

        try {
            // Fazer a requisição POST para a API do LinkedIn
            $response = $client->post('https://api.linkedin.com/v2/ugcPosts', [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                    'X-Restli-Protocol-Version' => '2.0.0',
                ],
                'json' => $postContent,
            ]);

            // Verificar a resposta da API
            if ($response->getStatusCode() == 201) {
                return response()->json(['message' => 'Postagem com link realizada com sucesso!'], 201);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao fazer a postagem: ' . $e->getMessage()], 500);
        }
    }
    
    

public function PublicarLinkedIn2($post, $link = null, $imagePath = null, $_pageId = '71657080')
{
    $accessToken = env('LINKEDIN_ACCESS_TOKEN');
    $pageId = $_pageId;

    $client = new Client();

    try {
        // Verifica se é necessário fazer upload da imagem
        $media = [];
        if ($imagePath) {
            $assetUrn = $this->uploadImageToLinkedIn($client, $accessToken, $pageId, $imagePath);
            if (!$assetUrn) {
                return response()->json(['error' => 'Erro ao fazer o upload da imagem.'], 500);
            }

            $media[] = [
                "status" => "READY",
                "media" => $assetUrn,
            ];
        }

        // Monta o conteúdo da postagem
        $postContent = [
            "author" => "urn:li:organization:" . $pageId,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary" => [
                        "text" => $post
                    ],
                    "shareMediaCategory" => $imagePath ? "IMAGE" : "ARTICLE",
                    "media" => $imagePath ? $media : [
                        [
                            "status" => "READY",
                            "originalUrl" => $link
                        ]
                    ]
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
            ]
        ];

        // Faz a requisição POST para criar a postagem
        $response = $client->post('https://api.linkedin.com/v2/ugcPosts', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
                'X-Restli-Protocol-Version' => '2.0.0',
            ],
            'json' => $postContent,
        ]);

        if ($response->getStatusCode() == 201) {
            return response()->json(['message' => 'Postagem realizada com sucesso!'], 201);
        }

    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao fazer a postagem: ' . $e->getMessage()], 500);
    }
}

/**
 * Realiza o upload de uma imagem para o LinkedIn e retorna o asset URN.
 */
private function uploadImageToLinkedIn($client, $accessToken, $pageId, $imagePath)
{
    try {
        // Registrar o upload da imagem
        $registerResponse = $client->post('https://api.linkedin.com/v2/assets?action=registerUpload', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "registerUploadRequest" => [
                    "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
                    "owner" => "urn:li:organization:$pageId",
                    "serviceRelationships" => [
                        [
                            "relationshipType" => "OWNER",
                            "identifier" => "urn:li:userGeneratedContent",
                        ],
                    ],
                ],
            ],
        ]);

        $registerData = json_decode($registerResponse->getBody(), true);
        $uploadUrl = $registerData['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
        $assetUrn = $registerData['value']['asset'];

        // Fazer upload da imagem
        $client->post($uploadUrl, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'body' => fopen($imagePath, 'r'),
        ]);

        return $assetUrn;
    } catch (\Exception $e) {
        return null;
    }
}

    
    
    public function setCategories($Id, $Description)
    {
        //$dados = json_decode('[{"id":"' . $Id . '","description":"' . $Description . '"} ]', true);

        //$Description = $dados[0]['description'];
        
        echo($Description);

        $Categories = $this->CategoriaViaGemini($Description);

        foreach($Categories as $Category)
        {
            //Chamar api para a categoria
            $client = new Client();

            $response = $client->request('POST', 'https://ao.empregosyoyota.net/api/category_job/create', ['verify' => false,
                'json' => [
                    'category_id' => $Category,
                    'job_id' => $Id,
                ]
            ]);
        }
        
    }
    
    public function DescricaoLimpa($Text)
    {
		$NovaDescricao = str_replace("<br>", "\n<br>", $Text);
		$NovaDescricao = str_replace("</p>", "</p>\n", $NovaDescricao);
		$NovaDescricao = str_replace("</h1>", "</h1>\n", $NovaDescricao);
		$NovaDescricao = str_replace("</h2>", "</h2>\n", $NovaDescricao);
		$NovaDescricao = str_replace("</h3>", "</h3>\n", $NovaDescricao);
		$NovaDescricao = str_replace("</li>", "</li>\n", $NovaDescricao);
		$NovaDescricao = explode('----------', $NovaDescricao)[0];
		$NovaDescricao = strip_tags($NovaDescricao);
		$NovaDescricao = str_replace("&nbsp;", "", $NovaDescricao);
		
		return $NovaDescricao;
    }
	
	function TituloViaGemini(){

        $api_key = env('GOOGLE_API_KEY_1');
        $api_key_rosa = env('GOOGLE_API_KEY_ROSA'); $api_key_albertina = env('GOOGLE_API_KEY_ALBERTINA');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $client = new Client();

        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'key' => $api_key_rosa,
            ],
            'json' => [
                "contents" => [
                    [
                        "parts" => [
                            [
                                "text" => "Fiz um artigo partilhando as vagas de emprego do dia de " . Carbon::now()->format('d-m-Y') . ". Crie um título chamativo para este artigo e no título deve conter o dia, mês e ano (O mês da data deve ser descrito) e me dê o dado no seguinte formato JSON: {title: TITULO_DO_ARTIGO} ",
                            ],
                        ],
                    ],
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "topK" => 1,
                    "topP" => 1,
                    "maxOutputTokens" => 2048,
                    "stopSequences" => [],
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                ],
            ],
        ]);

        // O conteúdo da resposta pode ser acessado assim:
        $responseBody = $response->getBody()->getContents();
        $jsonObject = json_decode($responseBody);
        $PegarJSON = json_decode($jsonObject->candidates[0]->content->parts[0]->text);
        
        if(!isset($PegarJSON->title) || !isset($PegarJSON))
        {
            throw ValidationException::withMessages(['Title não existe']);
        }
        
        try {
            return $PegarJSON->title;
        } catch (Exception $e) {
            // Trate erros aqui
            return $this->TituloViaGemini();
        }

    }
    
    function CategoriaViaGemini($Descricao){

        $api_key = env('GOOGLE_API_KEY_1');
        $api_key_rosa = env('GOOGLE_API_KEY_ROSA'); $api_key_albertina = env('GOOGLE_API_KEY_ALBERTINA'); $api_key_simao = env('GOOGLE_API_KEY_SIMAO');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $client = new Client();

        $response = $client->request('POST', $url, ['verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'key' => $api_key_albertina,
            ],
            'json' => [
                "contents" => [
                    [
                        "parts" => [
                            [
                                //retirei o senso de urgencia com "Deve também incluir um senso de urgência nos titulos."
                                "text" => 'de acordo com a seguinte lista de categoria: (1, \'Admininistração e Gestão\'), (2, \'Administrativo e Secretariado\'), (3, \'Agropecuária e Pesca\'), (4, \'Alimentação e Nutrição\'), (5, \'Ambiente\'), (6, \'Arquitectura\'), (7, \'Atendimento ao Cliente\'), (8, \'Aviação\'), (9, \'Banca e Seguros\'), (10, \'Comercial\'), (11, \'Comunicação Social\'), (12, \'Concurso Público\'), (13, \'Construção Civil\'), (14, \'Consultoria\'), (15, \'Contabilidade\'), (16, \'Costura\'), (17, \'Culinária\'), (18, \'Cozinha\'), (19, \'Design\'), (20, \'Desportos\'), (21, \'Economia\'), (22, \'Educação e Ensino\'), (23, \'Electricidade\'), (24, \'Engenharia\'), (25, \'Estágio\'), (26, \'Estética\'), (27, \'Farmácia\'), (28, \'Fotografia e Vídeo\'), (29, \'Freelancer\'), (30, \'Hotelaria e Turismo\'), (31, \'Informática e Tecnologias\'), (32, \'Jurídico\'), (33, \'Línguas\'), (34, \'Logística\'), (35, \'Marketing e Publicidade\'), (36, \'Marketing Digital\'), (37, \'Mecánica\'), (38, \'Medicina\'), (39, \'Transportes\'), (40, \'Recursos Humanos\'), (41, \'Auditoria\'), (42, \'Petrolífero\'), (43, \'Finanças\'), (44, \'Beleza\'); em quais categorias se encaixam a seguinte proposta de emprego, baseando na descrição, requisitos e competÊncias. escolha apenas as Categorias que se aplicam à proposta de emprego e retorne os dados num json no seguinte formato { categories: [id1, id2, id3...]}. eis a descrição: '. $Descricao,
                            ],
                        ],
                    ],
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "topK" => 1,
                    "topP" => 1,
                    "maxOutputTokens" => 2048,
                    "stopSequences" => [],
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                ],
            ],
        ]);

        // O conteúdo da resposta pode ser acessado assim:
        $responseBody = $response->getBody()->getContents();
        $jsonObject = json_decode($responseBody);
        $jsonstring = strtr($jsonObject->candidates[0]->content->parts[0]->text, ['```' => ""]);
        $jsonstring = strtr($jsonstring, ['json' => ""]);
        $PegarJSON = json_decode($jsonstring);

        if(!isset($PegarJSON->categories) || !isset($PegarJSON))
        {
            throw ValidationException::withMessages(['Categoria Não Existe']);
        }

        try {
            return $PegarJSON->categories;
        } catch (Exception $e) {
            // Trate erros aqui
            return $this->CategoriaViaGeminiViaGemini($Descricao);
        }

    }
    
    function DescricaoVagaViaGemini($Descricao){

        $api_key = env('GOOGLE_API_KEY_1');
        $api_key_rosa = env('GOOGLE_API_KEY_ROSA'); $api_key_albertina = env('GOOGLE_API_KEY_ALBERTINA');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $client = new Client();

        $response = $client->request('POST', $url, ['verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'key' => $api_key_rosa,
            ],
            'json' => [
                "contents" => [
                    [
                        "parts" => [
                            [
                                "text" => 'tenho o seguinte texto e pretendo transformar em linguagem de marcação (formataçaõ html) com bolds e titulos, etc para gravar como artigo na base de dados do meu blog, por favor faça isso e envie os dados no seguinte formato JSON: {description: DESCRICÃO_EM_HYPERTEXTO}: ' . $Descricao,
                            ],
                        ],
                    ],
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "topK" => 1,
                    "topP" => 1,
                    "stopSequences" => [],
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                ],
            ],
        ]);

        // O conteúdo da resposta pode ser acessado assim:
        $responseBody = $response->getBody()->getContents();
        $jsonObject = json_decode($responseBody);
        $jsonstring = strtr($jsonObject->candidates[0]->content->parts[0]->text, ['```' => ""]);
        $jsonstring = strtr($jsonstring, ['json' => ""]);
        $PegarJSON = json_decode($jsonstring);

        if(!isset($PegarJSON->description) || !isset($PegarJSON))
        {
            throw ValidationException::withMessages(['Descrição não existe']);
        }

        try {
            return $PegarJSON->description;
        } catch (Exception $e) {
            // Trate erros aqui
            return $this->DescricaoVagaViaGemini($Descricao);
        }

    }
    
    function TituloVagaViaGemini($TituloAntigo = 'Administrador'){

        $api_key = env('GOOGLE_API_KEY_1');
        $api_key_rosa = env('GOOGLE_API_KEY_ROSA'); $api_key_albertina = env('GOOGLE_API_KEY_ALBERTINA');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $client = new Client();

        $response = $client->request('POST', $url, ['verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'key' => $api_key_rosa,
            ],
            'json' => [
                "contents" => [
                    [
                        "parts" => [
                            [
                                //retirei o seso de urgencia com "Deve também incluir um senso de urgência nos titulos."
                                "text" => 'Crie um titulo para esta vaga de emprego: "' . $TituloAntigo . '". Os titulos nunca devem ser na primeira pessoa e devem ter o estilo parecido com: Vaga para xxx, precisa-se de, grande oportunidade para, opotunidade urgente, etc. Quero o dado no seguinte formato json: {title: TITULO_DA_VAGA}',
                            ],
                        ],
                    ],
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "topK" => 1,
                    "topP" => 1,
                    //"maxOutputTokens" => 2048,
                    "stopSequences" => [],
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                ],
            ],
        ]);

        // O conteúdo da resposta pode ser acessado assim:
        $responseBody = $response->getBody()->getContents();
        $jsonObject = json_decode($responseBody);
        $jsonstring = strtr($jsonObject->candidates[0]->content->parts[0]->text, ['```' => ""]);
        $jsonstring = strtr($jsonstring, ['json' => ""]);
        $PegarJSON = json_decode($jsonstring);

        if(!isset($PegarJSON->title) || !isset($PegarJSON))
        {
            throw ValidationException::withMessages(['Title não existe']);
        }

        try {
            return $PegarJSON->title;
        } catch (Exception $e) {
            // Trate erros aqui
            return $this->TituloVagaViaGemini($TituloAntigo);
        }

    }

    function ImagemVagaViaGemini($EmpresaArtigo = "Open mind"){

        $api_key = env('GOOGLE_API_KEY_1');
        $api_key_rosa = env('GOOGLE_API_KEY_ROSA'); $api_key_albertina = env('GOOGLE_API_KEY_ALBERTINA');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $client = new Client();

        $response = $client->request('POST', $url, ['verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'key' => $api_key_rosa,
            ],
            'json' => [
                "contents" => [
                    [
                        "parts" => [
                            [
                                "text" => 'a empresa "' . $EmpresaArtigo . '" está a recrutar, o meu sistema precisa de escolher uma imagem que represente o logo da empresa e de acordo com a lista a seguir escolha um nome de ficheiro que represente a empresa "' . $EmpresaArtigo . '" VOCÊ DEVE VERIFICAR O NOME DA EMPRESA EXATA DA LISTA e envie os dados no seguinte formato json {"name":"FICHEIRO_ESCOLHIDO.JPG"} e se não existir a empresa na lista o json retornado deve ser : {"name":"default.jpg"} 1K4S-One-Key-For-Solutions.jpg|td-hotels.png|Academia de Gestão de Negocios.PNG|ADPP Angola.PNG|afaviasangola-1.png|AFC Mercy.PNG|Africell.png|Algoa Cabinda Fabrication Services – Serviços Petrolíferos.PNG|Alimenta Angola.PNG|Alsof.PNG|Angoalissar.PNG|Angola-lng.png|Angolaca.PNG|Angomart.PNG|Associação Africana dos Países Produtores de Diamantes (ADPA).PNG|Azule-Energy.jpg|Baker Hughes.PNG|Baobabay.PNG|Be talent.PNG|bfa.PNG|Biocom.PNG|Biscateiro.PNG|Bodiva.PNG|Brands UP.JPG|BrasAfrica.PNG|Brechó Angola.JPG|Briopul.jpg|Buildcom.PNG|Bureau Veritas.PNG|Candando.JPG|Carpinangola.png|Carrinho.png|CD Master Center.PNG|Cegoc - Beyond Knowledge.JPG|Centro óptico.PNG|Chevron.jpg|Cimenfort.JPG|cnorey-coutinho.jpg|Colégio Infante Santo.PNG|CondosHome.JPG|Conduril.PNG|Cpc Africa.PNG|CUAMM.JPG|Dahua Tecnology.PNG|Divita Care.PNG|Dof subsea.PNG|Edicenter-Angola.png|ElephantBet.JPG|Embaixada dos Estados Unidos de América em Angola.PNG|Engconsult.JPG|engevia.jpg|Epalmo-Angola.png|Epic sana.PNG|Epic sana.webp|Epinosul.PNG|EPPM-Angola-SA-1.png|Escola Portuguesa Lubango.PNG|Escola-Camilo-Castelo-Branco-logo-Ango-Emprego.jpg|ESPACIE SERVICES.PNG|Espaes Angola.JPG|Esso Exploration Angola.JPG|explicolandia-logo.png|Farmacia Qualidade.JPG|Fidelidade.PNG|Fresmart.PNG|Friedlander.JPG|GAC Motors.JPG|Genuine farma.jpg|GOHSPROTEC.PNG|Griner.PNG|Grupo Casais Angola.PNG|Grupo Chicoil, SA..PNG|Grupo DG.JPG|Grupo Naval.JPG|Grupo Simples Oil.PNG|Halliburton.PNG|Heetch.PNG|hidroplanalto.png|HRD Angola.png|HRM Consulting.PNG|IEP-INVESTIMENTOS-E-PARTICIPACOES-LDA..png|Imetro.png|inCentea.jpg|INE.jpg|Instituto Superior Politécnico Atlântida.PNG|InterContinental Hotel.PNG|ISPTEC.PNG|Jetour.JPG|Kept.JPG|KixiCredito-Angola-S.A..jpg|Lan Security.PNG|Lassarat.PNG|Logotipo oficial.JPG|Lubritec.PNG|Maersk.JPG|Mariango Lda.PNG|Maxi.PNG|MCA Group.jpg|medtech engenharia hospitala.jpg|minsa.png|Mitchell-Drilling-Angola-Lda.jpg|Mitrelli.jpg|Mota-Engil.PNG|Multipessoal Angola.PNG|Nestlé.PNG|Nox Angola.PNG|Oceaneering Angola, S.PNG|OEC.JPG|Omatapalo.webp|One Select.JPG|Open-Mind-Consultoria.webp|Openmind.PNG|OPS - Serviços de Produção de Petróleos.PNG|Ora Invest, Lda.PNG|Organizacao-Africana-de-Produtores-de-Petroleo-APPO.jpeg|Organizacao-Medicos-Sem-Fronteiras-da-Suica-em-Angola.jpg|P.R.I. - Precision Recruitment International.PNG|PCA.JPG|PEP Africa.JPG|Petrolog.PNG|Procenter.PNG|ProLog.JPG|PSI.JPG|Pumangol.PNG|REFRIANGO.png|Sanlam.jpg|SAPURA-ENERGY-ANGOLA-LDA..jpg|SBM.PNG|Seaside.PNG|sgs.png|siemens-energy.webp|SIR – Segurança Industrial e Residencial.jpg|slb.JPG|Sociedade Mineira Lulo.PNG|Sodosa.JPG|Sonils.PNG|Spap angola Grafica.JPG|Standard Bank.PNG|STAPEM-Offshore-logo.jpg|TDGI.png|Tecnovia.PNG|Test Angola.JPG|Toptech Tintas.JPG|TotalEnergies.jpg|Transocean.png|Universidade-Oscar-Ribas.png|Ution.PNG|Vaga.jpg|Valaris.JPG|Vetify.JPG|Victory Oil & Energy.png|Weatherford.jpg|Webcor Group.JPG|Zopo.PNG',
                            ],
                        ],
                    ],
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "topK" => 1,
                    "topP" => 1,
                    "maxOutputTokens" => 2048,
                    "stopSequences" => [],
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                ],
            ],
        ]);

        // O conteúdo da resposta pode ser acessado assim:
        $responseBody = $response->getBody()->getContents();
        $jsonObject = json_decode($responseBody);
        $jsonstring = strtr($jsonObject->candidates[0]->content->parts[0]->text, ['```' => ""]);
        $jsonstring = strtr($jsonstring, ['json' => ""]);
        $PegarJSON = json_decode($jsonstring);


        try {
            return $PegarJSON->name;
        } catch (Exception $e) {
            // Trate erros aqui
            return $this->TituloVagaViaGemini($EmpresaArtigo);
        }

    }
    
    function GetOnContent($Content){

        $api_key = env('GOOGLE_API_KEY_1');
        $api_key_rosa = env('GOOGLE_API_KEY_ROSA'); $api_key_albertina = env('GOOGLE_API_KEY_ALBERTINA');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $client = new Client();

        $response = $client->request('POST', $url, ['verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'key' => $api_key_rosa,
            ],
            'json' => [
                "contents" => [
                    [
                        "parts" => [
                            [
                                //PROMPT PARA IA
                                "text" => 'olhe a descrição do seguinte emprego: ' . $Content . ' leia os dados e retire o endereço para aplicar à vaga (email ou link) e me dê os dados em json no seguinte formato  {email_or_link: EMAIL_OR _LINK}',
                            ],
                        ],
                    ],
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "topK" => 1,
                    "topP" => 1,
                    //"maxOutputTokens" => 2048,
                    "stopSequences" => [],
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE",
                    ],
                ],
            ],
        ]);

        // O conteúdo da resposta pode ser acessado assim:
        $responseBody = $response->getBody()->getContents();
        $jsonObject = json_decode($responseBody);
        $jsonstring = strtr($jsonObject->candidates[0]->content->parts[0]->text, ['```' => ""]);
        $jsonstring = strtr($jsonstring, ['json' => ""]);
        $PegarJSON = json_decode($jsonstring);

        if(!isset($PegarJSON->email_or_link) || !isset($PegarJSON))
        {
            throw ValidationException::withMessages(['Title não existe']);
        }

        try {
            return $PegarJSON->email_or_link;
        } catch (Exception $e) {
            // Trate erros aqui
            return $this->GetOnContent($Content);
        }

    }
    
    function iniciarPagamento()
{
    // ***** MUDE ESTE URL PARA O URL REAL DA SUA API NO CPANEL *****
    $apiUrl = 'https://api.seusite.com/api/iniciar-pagamento';

    // O corpo da requisição que a sua API espera
    $payload = [
        'numeroTelefone' => '940590895',
    ];

    try {
        // Inicializa o Guzzle
        $client = new Client();

        // Faz a chamada POST para a sua API
        $response = $client->post($apiUrl, [
            'json' => $payload,    // Envia os dados como JSON
            'timeout' => 120,      // Timeout de 2 minutos (ESSENCIAL)
        ]);

        // Se a chamada para a sua API funcionou (código 200)
        // Pega a resposta da sua API Node.js
        $apiResponseData = json_decode($response->getBody()->getContents(), true);

        // Define o código de sucesso para a resposta HTTP desta função
        http_response_code(200);

        // Retorna uma resposta de sucesso em formato JSON
        return json_encode([
            'status' => 'sucesso',
            'message' => 'A automação foi acionada.',
            'dados_da_api' => $apiResponseData,
        ]);

    } catch (RequestException $e) {
        // Se a API retornou um erro (4xx, 5xx) ou houve falha de rede
        $errorMessage = 'Erro ao chamar a API de pagamento: ';
        if ($e->hasResponse()) {
            // Pega a mensagem de erro que a sua API Node.js enviou
            $errorMessage .= (string) $e->getResponse()->getBody();
            // Define o código de erro que a sua API Node.js enviou
            http_response_code($e->getResponse()->getStatusCode());
        } else {
            // Se não houve resposta (ex: falha de DNS, timeout)
            $errorMessage .= $e->getMessage();
            http_response_code(503); // Service Unavailable
        }

        // Retorna a mensagem de erro em formato JSON
        return json_encode(['status' => 'erro', 'message' => $errorMessage]);
    }
}
}
