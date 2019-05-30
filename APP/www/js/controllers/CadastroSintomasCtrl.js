app.controller('CadastroSintomasCtrl', function ($scope, ionicMaterialInk, $http, $ionicSideMenuDelegate, $ionicPopup, $ionicModal, $state, $ionicScrollDelegate) {
	 // Disparando ação de load
	 //$scope.carregando();
	 // Lista de exames
	 $scope.usuarioLogado(true)
	$scope.sintomasData = {};	 

	/** MODAL DE CONFIMAÇÃO **/
	$ionicModal.fromTemplateUrl('templates/cadastrar-sintomas-confirmacao.html', {
	scope: $scope
	}).then(function(modal) {
	$scope.modal = modal;
	});
	/** FIM MÉTODO PARA VALIDAR AS INFORMAÇOES **/
	$scope.listaSintomas=[];
	$http({
		method: "GET",
	    timeout:$scope.timeout,
	    url: $scope.strUrlServico + Constantes.APP_SERVICE_LISTAR_TIPOS_SINTOMAS,
	    headers: Util.headers($scope.token)
	 }).then(function(response) {
		 console.log($scope.strUrlServico + Constantes.APP_SERVICE_LISTAR_TIPOS_SINTOMAS );
		 if(response.data.bolRetorno == true){
			 $scope.classificacaoSintomas = response.data.result;
			}
		}, function(response) {
				// Mensagem de erro
			   $scope.falhaCarregamento(response);
		});

		/**
	  * Método que irá formatar a data
	  */

    
     //Listar sintomas conforme o TIPO
	$http({
		method: "GET",
	    timeout:$scope.timeout,
		url: $scope.strUrlServico + Constantes.APP_SERVICE_LISTAR_SINTOMAS + "?intIdTipo=10" ,  //$scope.intIdTipo,
	    headers: Util.headers($scope.token)
	 }).then(function(response) {
		 console.log($scope.strUrlServico + Constantes.APP_SERVICE_LISTAR_SINTOMAS + "?intIdTipo=10"); //+$scope.intIdTipo);
		 if(response.data.bolRetorno == true){
			 $scope.listaSintomas = response.data.result;
			}
		}, function(response) {
				// Mensagem de erro
			   $scope.falhaCarregamento(response);
		});


/*      $scope.listaSintomas = [ { id: 10, descricao: "Nauseas"},
							  { id: 11, descricao: "Diarreia1"},
							  { id: 12, descricao: "Diarreia2"},
							  { id: 13, descricao: "Diarreia3"},
							  { id: 14, descricao: "Diarreia4"},
							  { id: 15, descricao: "Diarreia5"},
							  { id: 16, descricao: "Diarreia6"},
						      { id: 12, descricao: "Constipação"}];  */
	 //exemplo em minhas notificacoes

	 //criar outra lista com $scope.opcoes => sintomas, sentimentos etc.
/* 	 $scope.classificacaoSintomas = [{	id: 10,
						 		descricao: "Sintoma"},
							 		{  id: 20,
						 		descricao: "Estado"}];  */
						  
	 
	 $scope.scrollMainToTop = function() {
		 console.log("aqui");
		    $ionicScrollDelegate.$getByHandle('mainScroll').scrollTop();
	 };	  
	 $scope.doRefresh = function() {
		  // $scope.carregarNotificacoes();
	      // TODO
	 };
      //reuniao de 13-abr-19 - Tiago
	 $scope.validarSintomas = function(){
		// a fazer
		console.log($scope.sintomasData);
		$scope.confirmarCadastro()
	 }

	$scope.confirmarCadastro = function() {
		$scope.modal.show();
		$scope.configurarConfirmacao();
	};

	$scope.closeConfirmar = function() {
		$scope.modal.hide();
		$scope.removerConfirmacao();
	};

/******* FIM DO MÉTODO QUE IRÁ CALCULAR O PRAZO **************/
	 /*** MÉTODO DE SALVAR OS DADOS ****/
	 $scope.salvar = function(){
		
		// Disparando ação de load
		$scope.carregando();
		// Postando para URL
    	$http({
			method: "POST",
		    timeout:$scope.timeout,
		    data: 'dadosSintoma=' + JSON.stringify($scope.SintomaData) + "&usuario_id="+$scope.loginData.id,
		    url: $scope.strUrlServico + Constantes.APP_SERVICE_CADASTRAR_SINTOMAS ,
		    headers: Util.headers($scope.token)
			
		})



		.then(function(response) {
			// Disparando ação de load
			$scope.carregado();
			bolRetorno = false;
			mensagem   = "";
			if(response.data.bolRetorno == true){
				bolRetorno = true;
				mensagem = "Cadastro Realizado Com Sucesso!";
			}else{
				mensagem = response.data.strMensagem;
			}
			
			var alertPopup = $ionicPopup.alert({
				title: (bolRetorno) ? 'Sucesso' : "Erro",
				template: mensagem
			});
			alertPopup.then(function(res) { });
			$scope.closeConfirmar();
			// Redirecionando para o inicio
			setTimeout(function(){
				// Redirecionado para o inicio
				if(bolRetorno){
					$scope.exameData = {};
				}
			}, 1500);
		}, function(response) {
			console.log(response);
			// Disparando ação de load
			$scope.carregado();
			
			// Mensagem de erro
			$scope.falhaCarregamento(response, true);
		});
	 }
	 /*** FIM MÉTODO SALVAR OS DADOS ***/

	

});
