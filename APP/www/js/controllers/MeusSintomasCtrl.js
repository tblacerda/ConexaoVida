app.controller('MeusSintomasCtrl', function ($scope, ionicMaterialInk, $http, $ionicSideMenuDelegate, $ionicPopup, $ionicModal, $state, $ionicScrollDelegate) {
	 // Disparando ação de load
	 $scope.carregando();
	 // Lista de exames
	/* $scope.listaSintomas = [{ id: 10, descricao: "Nauseas"},
													{ id: 11, descricao: "Diarreia1"},
													{ id: 12, descricao: "Diarreia2"},
													{ id: 13, descricao: "Diarreia3"},
													{ id: 14, descricao: "Diarreia4"},
													{ id: 15, descricao: "Diarreia5"},
													{ id: 16, descricao: "Diarreia6"},
													{ id: 17, descricao: "Diarreia7"},
						              { id: 18, descricao: "Constipação"}]; */
	 //exemplo em minhas notificacoes
	 //criar outra lista com $scope.opcoes => sintomas, sentimentos etc.

	 /* 	 $scope.classificacaoSintomas = [{	id: 10,
						 		descricao: "Sintoma"},
							 		{  id: 20,
						 		descricao: "Estado"}]; */
	
	/* $scope.meusSintomas = [{ id: 10,
    descricao: "Nauseas",
    data_cadastro: '02/03/2019 09:53',
    intensidade: 5
  },
  {
    id: 11,
    descricao: "Nauseas",
    data_cadastro: '03/03/2019 09:53',
    intensidade: 9
  },
  {
    id: 11,
    descricao: "Nauseas",
    data_cadastro: '04/03/2019 09:53',
    intensidade: 2
  }
]; */

/** Método que irá recuperar os exames da base **/
	 $scope.classificacaoSintomas = function(){
		// Recuperando os dados do usuário
		$scope.carregando();
		 $http({
				method: "GET",
			    timeout:$scope.timeout,
			    url: $scope.strUrlServico + Constantes.APP_SERVICE_LISTAR_TIPOS_SINTOMAS,
			    headers: Util.headers($scope.token)
		 }).then(function(response) {
			 	// Disparando ação de load
				$scope.carregado();
				 if(response.data.bolRetorno == true){
					 // Caso encontre o usuário
					 $scope.classificacaoSintomas = response.data.result;
				 }else{
					var alertPopup = $ionicPopup.alert({
						title: "Erro",
						template: "Nenhum Sintoma Cadastrado!"
					});
					alertPopup.then(function(res) { });
					}
		 }, function(response) {
			// Mensagem de erro
			$scope.falhaCarregamento(response);
		 });
	 }

$scope.carregando();
/** Método que irá recuperar os exames da base **/
	 $scope.listaSintomas = function(){
		// Recuperando os dados do usuário
		 $http({
				method: "GET",
			    timeout:$scope.timeout,
					//url: $scope.strUrlServico + Constantes.APP_SERVICE_LISTA_SINTOMAS_PACIENTE + "?intIdUsuario="+$scope.intIdUsuario,"?intIdUsuario="+$scope.loginData.id
					url: $scope.strUrlServico + Constantes.APP_SERVICE_LISTA_SINTOMAS_PACIENTE + "?intIdUsuario="+$scope.loginData.id, // "?intIdUsuario="+$scope.loginData.id
			    headers: Util.headers($scope.token) 
		 }).then(function(response) {
			 	// Disparando ação de load
				$scope.carregado();
				 if(response.data.bolRetorno == true){
					 // Caso encontre o usuário
					 $scope.meusSintomas = response.data.result;

				 }else{
					var alertPopup = $ionicPopup.alert({
						title: "Erro",
						template: "Nenhum Sintoma Cadastrado!"
					});
					alertPopup.then(function(res) { });
					}
		 }, function(response) {
			// Mensagem de erro
			$scope.falhaCarregamento(response);
		 });
	 }
	 // Recuperando os exames
	$scope.listaSintomas();  // RETIRAR RETIRAR 1111
	 $scope.classificacaoSintomas();

	 

	 $scope.scrollMainToTop = function() {
		 console.log("aqui");
		    $ionicScrollDelegate.$getByHandle('mainScroll').scrollTop();
	 };	  
	 $scope.doRefresh = function() {
		  // $scope.carregarNotificacoes();
	      // TODO
	 };

	 $scope.validar = function(){
		// Validando os campos 
		$scope.confirmarCadastro();
	}

	$scope.confirmarCadastro = function() {
		$scope.modal.show();
		$scope.configurarConfirmacao();
	};

	/** FIM MÉTODO PARA VALIDAR AS INFORMAÇOES **/


	});
//$scope 