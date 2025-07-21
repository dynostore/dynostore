/*Recuperación IDA con cualquier n,m sobre campo GF(16), GF(8).
El programa se ejecuta de la siguiente manera desde la linea de comandos:
./Rec archivo.png 8 D0 D1 D2*/
#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <sys/time.h>
#include "Libraries/LogAdministrator.h"

struct timeval tiempo1, tiempo2;
int microsegundos;

/* VARIABLES GLOBALES */
typedef unsigned int ** Matriz; 
typedef unsigned int * Vector;
unsigned short int N,M,k,campo,alpha_base,Relleno;
Matriz MatrizTransf;
Vector Tabla_Alphas;
Vector LAlphas;
FILE **Salidas;

/*FUNCIONES*/
void GeneraCampo(unsigned int k);
void Generar_Alphas(unsigned short int tam_camp);
void Recupera8(char **argv);
void Recupera16(char **argv);
void Invertir_Gauss(void);

Vector Crear_Vector(unsigned int N);
void Imprime_Vector(Vector Arreglo, int n);
Matriz Crear_Matriz(unsigned short int n, unsigned short int m);
void Imprime_Matriz(Matriz Mat,unsigned short int n,unsigned short int m);
void Liberar_Matriz(Matriz Mat, unsigned short int n);

/*Operaciones con campos*/
void Suma_Resta_Renglon_G(unsigned int renglon,unsigned int aux[]);
void Constante_Renglon_G(unsigned int renglon,unsigned int constante,unsigned int aux[]);
void Dividir_Renglon_G(unsigned int renglon,unsigned int div);
unsigned int Suma_Resta(unsigned int a,unsigned int b);
unsigned int Multiplicacion(unsigned int a,unsigned int b);
unsigned int Division(unsigned int a, unsigned int b);

void main(int argc, char **argv)
{
	/*truct LogFile AccessLayerLog;
	char *logPath = "Salida/file1_1MB.csv";	
	char logContent[255];

	char headLine[300];
*/	
	FILE *in;
	Vector Lalphas2;
	int i,j,regLeidos;
	k=atoi(argv[2]);


	campo=pow(2,k)-1;
	GeneraCampo(k);
	
	in=fopen(argv[3],"rb");
	if(in==NULL)
		{
		printf("No se puede abrir disperso\n");
		exit(1);
		}


	gettimeofday(&tiempo1, NULL);	
	switch(k)
	{
		case 4:
			//Recupera4();
			break;
		case 8:
			regLeidos=fread(&Relleno,sizeof(char),1,in);
			regLeidos=fread(&N,sizeof(char),1,in);
			regLeidos=fread(&M,sizeof(char),1,in);
			rewind(in);//
			fclose(in);
			if(argc-3<M)
			{
				printf("Introduce mas dispersos\n");
				exit(1);
			}
			
			
			//getchar();
			Recupera8(argv);
			
			break;
		case 16:
			regLeidos=fread(&Relleno,sizeof(short int),1,in);
			regLeidos=fread(&N,sizeof(short int),1,in);
			regLeidos=fread(&M,sizeof(short int),1,in);
			rewind(in);
			fclose(in);
			if(argc-3<M)
			{
				printf("Introduce mas dispersos\n");
				exit(1);
			}
			Recupera16(argv);
			break;
		default:
			printf("No tengo ese campo\n");
			return;
	}
	/*Liberar memoria*/
	Liberar_Matriz(MatrizTransf,M);
	free(Tabla_Alphas);
	free(LAlphas);
	Tabla_Alphas=NULL;
	LAlphas=NULL;
	MatrizTransf=NULL;
	gettimeofday(&tiempo2, NULL);

	//Tiempo de respuesta
	microsegundos = ((tiempo2.tv_usec - tiempo1.tv_usec)  + ((tiempo2.tv_sec - tiempo1.tv_sec) * 1000000.0f));
    printf("%u\t%u \t%i\n",N,M,microsegundos/1000);


   /* strcpy(headLine, "");
	if(!fileExists(logPath)){
	sprintf(
		headLine, 
		"%s, %s, %s, %s",
			"N","M", "K","ResposeTime\n"
		);
	}
	//Se abre el archivo
	AccessLayerLog = openLog( logPath );
	if ( AccessLayerLog.status == 1 ){
		writeLine(AccessLayerLog, headLine);		
		strcpy(headLine, "");
	} else {
		printf("\t\tError: No se pudo abrir la bitacora\n");
		printf("Verificar que la ruta %s exista", logPath);
	}

	sprintf(logContent, "%s, %i, %i, %i", N, M, K, microsegundos/1000);


	if(AccessLayerLog.status == 1){ 	//Esta abierta
		//Se escribe el registro a la bitacora
		writeLine(AccessLayerLog, logContent);					
		closeLog(AccessLayerLog);
	} else { //No se encuentra abierta
		//Se muestra el contenido en la pantalla
		printf("Linea de la bitacora:\n%s",logContent);
	}*/
}
void Recupera8(char **argv)
{
	FILE *out;
	int i,j,regLeidos,regLeidos2,regEscritos,primera=1,q=0,k,l,bandera=0;
	unsigned char valor,valor2;
	unsigned char* BufferArchivo,*BufferSalida,* BufferArchivo2;
	unsigned int aux=0,bas;		
	
	MatrizTransf=Crear_Matriz(M,M*2);//Crea matriz
	
	Salidas=malloc(M*sizeof(FILE*));//cambie la N por M
   if(Salidas==NULL){
	 	printf("No se pudo reservar apuntadores a dispersos\n");
	 	exit(1);
	}
	for(i=0;i<M;i++){
		Salidas[i]=fopen(argv[i+3],"rb");
		if(Salidas[i]==NULL){
			printf("No puedo abrir algun disperso\n");
			exit(1);
		}
	}
	/*Tener que recorrer r,n,m de cada disperso*/
	for(i=0;i<M;i++){
		regLeidos=fread(&bas,sizeof(char),1,Salidas[i]);
		regLeidos=fread(&bas,sizeof(char),1,Salidas[i]);
		regLeidos=fread(&bas,sizeof(char),1,Salidas[i]);
	}
	for(i=0;i<M;i++){
		for(j=0;j<M;j++){
			regLeidos=fread(&valor,sizeof(char),1,Salidas[i]);
			MatrizTransf[i][j]=valor;
		}
	}
	for(i=0;i<M;i++){
		for(j=M;j<M*2;j++){
			if(i+M==j)
				MatrizTransf[i][j]=1;
			else
				MatrizTransf[i][j]=0;
		}
	}
	Invertir_Gauss();
	out=fopen(argv[1],"wb");
	if(out==NULL)
	{
		printf("No se puede crear el archivo de salida\n");
		exit(1);
	}
	
	if((BufferArchivo=(char*)malloc(M*sizeof(char)))==NULL)
		printf("No se pudo crear el buffer del archivo");
	if((BufferArchivo2=(char*)malloc(M*sizeof(char)))==NULL)
		printf("No se pudo crear el buffer del archivo");
	if((BufferSalida=(char*)malloc(M*sizeof(char)))==NULL)
		printf("No se pudo crear el buffer del archivo");
	
	
	for(i=0;i<M && regLeidos!=0;i++)/*cambie*/
		{
			regLeidos=fread(&valor,sizeof(char),1,Salidas[i]);
			if(regLeidos!=0){
			BufferArchivo[i]=valor;
			bandera++;
			}
		}		
		//Realizando operaciones
		for(i=0;i<M;i++){
			for(j=M;j<M*2;j++){
				aux=Suma_Resta(aux,Multiplicacion(MatrizTransf[i][j],BufferArchivo[q]));
				q++;
			}
		BufferSalida[i]=aux;
		q=0;
		aux=0;
		}
		
		for(i=0;i<M && regLeidos!=0;i++)/*cambie*/
		{
			regLeidos2=fread(&valor2,sizeof(char),1,Salidas[i]);
			if(regLeidos2!=0){
			BufferArchivo2[i]=valor2;
			bandera++;
			}
		}	
		
		do{
		if(regLeidos2!=0){
				for(i=0;i<M;i++){
						valor=BufferSalida[i];
						regEscritos=fwrite(&valor,sizeof(char),1,out);
				}
				for(i=0;i<M;i++)
				{
					BufferArchivo[i]=BufferArchivo2[i];
					
				}
		
				//Realizando operaciones
				for(i=0;i<M;i++){
					for(j=M;j<M*2;j++){
						aux=Suma_Resta(aux,Multiplicacion(MatrizTransf[i][j],BufferArchivo[q]));
						q++;
					}
				BufferSalida[i]=aux;
				q=0;
				aux=0;
				}
				
				for(i=0;i<M && regLeidos2!=0;i++)/*cambie*/
					{
						regLeidos2=fread(&valor2,sizeof(char),1,Salidas[i]);
						if(regLeidos2!=0){
						BufferArchivo2[i]=valor2;
						bandera=0;
						}
					}	
		}	
	}while(regLeidos2!=0);/*cambie*/
	/*ESCRITURA DE RELLENO*/
	valor=0;
			if(Relleno==0)
			{
			for(i=0;i<M;i++){
					valor=BufferSalida[i];
					regEscritos=fwrite(&valor,sizeof(char),1,out);
				}
			}
			if(Relleno!=0)
			{
				for(i=0;i<Relleno;i++){
					valor=BufferSalida[i];
					regEscritos=fwrite(&valor,sizeof(char),1,out);
				}
			}
	for(i=0;i<M;i++)//Cierre de archivo de cada disperso
		fclose(Salidas[i]);
	fclose(out);
	free(BufferArchivo);
	free(BufferSalida);
	free(Salidas);
	BufferArchivo==NULL;
	BufferSalida==NULL;
	Salidas==NULL;
}
/*********************************************************************************
	RECUPERA CON 16 BITS 
*********************************************************************************/
void Recupera16(char **argv)
{
	FILE *out;
	int i,j,regLeidos=-1,regLeidos2,regEscritos,primera=1,q=0,k,l,bandera=0;
	unsigned short int valor=0,valor2=0;
	unsigned short int* BufferArchivo,*BufferSalida,* BufferArchivo2;
	unsigned int aux=0,bas=0;		
	
	MatrizTransf=Crear_Matriz(M,M*2);//Crea matriz
	
	Salidas=malloc(M*sizeof(FILE*));//cambie la N por M
   if(Salidas==NULL){
	 	printf("No se pudo reservar apuntadores a dispersos\n");
	 	exit(1);
	}
	for(i=0;i<M;i++){
		Salidas[i]=fopen(argv[i+3],"rb");
		if(Salidas[i]==NULL){
			printf("No puedo abrir algun disperso\n");
			exit(1);
		}
	}
	/*Tener que recorrer r,n,m de cada disperso*/
	for(i=0;i<M;i++){
		regLeidos=fread(&bas,sizeof(short int),1,Salidas[i]);
		regLeidos=fread(&bas,sizeof(short int),1,Salidas[i]);
		regLeidos=fread(&bas,sizeof(short int),1,Salidas[i]);
	}
	for(i=0;i<M;i++){
		for(j=0;j<M;j++){
			regLeidos=fread(&valor,sizeof(short int),1,Salidas[i]);
			MatrizTransf[i][j]=valor;
		}
	}
	for(i=0;i<M;i++){
		for(j=M;j<M*2;j++){
			if(i+M==j)
				MatrizTransf[i][j]=1;
			else
				MatrizTransf[i][j]=0;
		}
	}
	Invertir_Gauss();
	out=fopen(argv[1],"wb");
	if(out==NULL)
	{
		printf("No se puede crear el archivo de salida\n");
		exit(1);
	}
	bas=0;//Contador para comparar simbolos
	if((BufferArchivo=(short int*)malloc(M*sizeof(short int)))==NULL)
		printf("No se pudo crear el buffer del archivo");
	if((BufferArchivo2=(short int*)malloc(M*sizeof(short int)))==NULL)
		printf("No se pudo crear el buffer del archivo");
	if((BufferSalida=(short int*)malloc(M*sizeof(short int)))==NULL)
		printf("No se pudo crear el buffer del archivo");
	
	
	for(i=0;i<M && regLeidos!=0;i++)/*cambie*/
		{	valor=0;
			regLeidos=fread(&valor,sizeof(char),2,Salidas[i]);
			if(regLeidos!=0){
			BufferArchivo[i]=valor;
			bandera++;
			}
		}
		
		//Realizando operaciones
		for(i=0;i<M;i++){
			for(j=M;j<M*2;j++){
				aux=Suma_Resta(aux,Multiplicacion(MatrizTransf[i][j],BufferArchivo[q]));
				q++;
			}
		BufferSalida[i]=aux;
		q=0;
		aux=0;
		}
		
		for(i=0;i<M && regLeidos!=0;i++)/*cambie*/
		{	valor=0;
			regLeidos2=fread(&valor2,sizeof(char),2,Salidas[i]);
			if(regLeidos2!=0){
			BufferArchivo2[i]=valor2;
			bandera++;
			}
		}	
		
		do{
		if(regLeidos2!=0){
				for(i=0;i<M;i++){
						valor=0;
						valor=BufferSalida[i];
						regEscritos=fwrite(&valor,sizeof(short int),1,out);
						bas++;
				}
				for(i=0;i<M;i++)
				{
					BufferArchivo[i]=BufferArchivo2[i];
					
				}
		
				//Realizando operaciones
				for(i=0;i<M;i++){
					for(j=M;j<M*2;j++){
						aux=Suma_Resta(aux,Multiplicacion(MatrizTransf[i][j],BufferArchivo[q]));
						q++;
					}
				BufferSalida[i]=aux;
				q=0;
				aux=0;
				}
				
				for(i=0;i<M && regLeidos2!=0;i++)/*cambie*/
					{
						valor=0;
						regLeidos2=fread(&valor2,sizeof(char),2,Salidas[i]);
						if(regLeidos2!=0){
						BufferArchivo2[i]=valor2;
						bandera=0;
						}
					}	
		}	
	}while(regLeidos2!=0);/*cambie*/
	/*ESCRITURA DE RELLENO*/
	valor=0;
				
			if(Relleno==0)
			{
				for(i=0;i<M;i++){
					valor=BufferSalida[i];
					if(i==M-1)
						{
						if(valor<=255){
								regEscritos=fwrite(&valor,sizeof(char),1,out);
							}
						else
							regEscritos=fwrite(&valor,sizeof(char),2,out);
						}
					else{
							regEscritos=fwrite(&valor,sizeof(char),2,out);
						}
				}
			}
			else
			{
				for(i=0;i<Relleno;i++){
					valor=BufferSalida[i];
					if(i==Relleno-1)
					{
						if(valor<=255){
							regEscritos=fwrite(&valor,sizeof(char),1,out);
						}
						else{
							regEscritos=fwrite(&valor,sizeof(char),2,out);
						}
					}
					else{
					valor=BufferSalida[i];
					regEscritos=fwrite(&valor,sizeof(char),2,out);
					}
				}
			}
	fclose(out);
	for(i=0;i<M;i++)//Cierre de archivo de cada disperso
		fclose(Salidas[i]);
	free(BufferArchivo);
	free(BufferSalida);
	free(Salidas);
	BufferArchivo==NULL;
	BufferSalida==NULL;
	Salidas==NULL;
	//printf("Liberacion de buffers\n");
}
void Invertir_Gauss(void)
{
	unsigned int aux[M*2]; //Es necesario para realizar operaciones del tipo F1=F1+3F2 ya que F2 no cambia pero debemos de multiplicar por una constante
	unsigned int i=0,j=0,renglon=0,columna=0,pivote_renglon;
	
	for(j=0;j<M;j++)
	{
		if(MatrizTransf[renglon][columna]!=1)//Si no es pivote
		{
			Dividir_Renglon_G(renglon,MatrizTransf[renglon][columna]);
		}
		
		pivote_renglon=renglon; //Marcar como pivote
		for(i=0;i<M;i++)
		{
			if(i!=j) //Si no es la diagonal
			{
				if(MatrizTransf[i][j]!=0) //Si no es un cero 
				{
					Constante_Renglon_G(pivote_renglon,MatrizTransf[i][j],aux);
					Suma_Resta_Renglon_G(i,aux);
				}
			}
		}
	renglon++;
	columna++;
	}	

}
void GeneraCampo(unsigned int k)
{
	unsigned int primitivo; //Polinomio primitivo de cada campo

	switch(k)
	{
		case 4:
			primitivo=19;
			alpha_base=primitivo&(unsigned int)campo;//se calcula con una máscara del alpha de grado máximo del campo
			break;
		case 8:
			primitivo=369;
			alpha_base=primitivo&(unsigned int)campo;
			break;
		case 16:
			primitivo=69643;
			alpha_base=primitivo&(unsigned int)campo;
			break;
	}
	Tabla_Alphas=Crear_Vector(campo+1); 
	LAlphas=Crear_Vector(campo+1);
	Generar_Alphas(k);
}
void Generar_Alphas(unsigned short int tam_camp)
{
	unsigned int indice=0,x=0; //Indice es utilizado en ambos vectores
	Tabla_Alphas[indice]=indice;
	LAlphas[indice]=indice;
for(indice=0;indice<campo;indice++)  //Recorrido de los vectores hasta el tamaño del campo
	{
		if(indice<tam_camp){
			Tabla_Alphas[indice]=(pow(2,indice));
			LAlphas[Tabla_Alphas[indice]]=indice;
			}
		else{
			if(indice==tam_camp)						 // b) si indice==tamaño del campo+1, obten la base del polinomio primitivo y agrega a Tabla_alphas e indexa LAlphas 
			{
				x=alpha_base;
				Tabla_Alphas[indice]=alpha_base;
				LAlphas[alpha_base]=indice;
			}
			else                                   // c) si indice>tamaño del campo+1, desplaza a la izq 1 vez( equivalente a multiplicar por 2
			{
				//x=2*x;
				x<<=1;
				if(x<=campo){	//c.1) si el resultado no excede alpha máximo del campo, dicho de otra manera, no excede el tamaño del campo
				//printf("indice:%i\n",indice);
				Tabla_Alphas[indice]=x;					//Entonces, agrega a Tabla_alphas e indexa L_Alphas
				LAlphas[x]=indice;
				}
			else{
				x=x&(campo); //c.2) si el resultado excede el tamaño del campo, se aplica una xor del valor del bit más significativo y agrega a Tabla_alphas e indexa LAlphas 
				x=x^alpha_base;
				Tabla_Alphas[indice]=x;
				LAlphas[x]=indice;
				}
			}
			
		}
	}
}
/***********************************************
		OPERACIONES CON CAMPOS
***********************************************/
void Suma_Resta_Renglon_G(unsigned int renglon,unsigned int aux[])
{
	unsigned int i;
	
	for(i=0;i<M*2;i++)
	{
		MatrizTransf[renglon][i]=Suma_Resta(MatrizTransf[renglon][i],aux[i]);
	}	
}
void Constante_Renglon_G(unsigned int renglon,unsigned int constante,unsigned int aux[])
{
	unsigned int j,tam_campo;
	
	tam_campo=pow(2,k); //Tamaño del campo
	
	for(j=0;j<M*2;j++)
	{
		aux[j]=Multiplicacion(MatrizTransf[renglon][j],constante);
	}
}
void Dividir_Renglon_G(unsigned int renglon,unsigned int div)
{
	unsigned int i;
	
	for(i=0;i<M*2;i++)
	{
		MatrizTransf[renglon][i]=Division(MatrizTransf[renglon][i],div);
	}
}
/****************************************************************
				OPERACIONES CON CAMPOS
****************************************************************/
unsigned int Suma_Resta(unsigned int a,unsigned int b)
{
	return (a^b);
}

unsigned int Multiplicacion(unsigned int a, unsigned int b)
{
	if ((a!=0)&&(b!=0)){
      return(Tabla_Alphas[((LAlphas[a]+LAlphas[b])%(campo))]);
      }
   else
      return(0);
}

unsigned int Division(unsigned int a, unsigned int b)
{
	if (b==0)
      return(1234);
   else
   if (a==0)
      return(0);
   else
   return(Tabla_Alphas[((LAlphas[a]+campo-LAlphas[b])%campo)]);
}

/***********************************************
		CREACION DE ESTRUCTURAS
***********************************************/
Matriz Crear_Matriz(unsigned short int n, unsigned short int m)
{
	int i;
	Matriz A;
	/*reservar memoria para numero de filas de tipo apuntador entero*/ 
	if((A=(Matriz)calloc(n,sizeof(unsigned int*)))==NULL)
	{
	 printf("No se pudo reservar memoria\n");
	 return NULL;
	}
	//Recorrer filas 
	for(i=0;i<n;i++)
	{
		/*reservar memoria para numero de columnas de tipo entero*/
		if((A[i]=(unsigned int*)calloc(2*m,sizeof(unsigned int)))==NULL) 
			return NULL;
	}
	return A;
}
Vector Crear_Vector(unsigned int N)
{
	Vector A;
	if((A=(Vector)malloc(N*sizeof(unsigned int)))==NULL)
	{
	 printf("No se pudo reservar memoria\n");
	 return NULL;
	}
	else{
		return A;}
}
void Imprime_Matriz(Matriz Mat,unsigned short int n,unsigned short int m)
{
	int i,j;
	for(i=0;i<n;i++){
		for(j=0;j<m;j++)
			{printf("%u ",Mat[i][j]);}
		printf("\n");
		}
	printf("\n");
}
void Imprime_Vector(Vector Arreglo, int n)
{
	int i;
	for(i=0;i<n;i++)
	{
		printf("%u,",Arreglo[i]);
	}
	printf("\n");	
}
void Liberar_Matriz(Matriz Mat, unsigned short int n)
{
	int i;
	/*Recorrer todos los punteros de arreglos y liberar uno por uno*/
	for(i=0;i<n;i++){
		free(Mat[i]);}
	/*Al final liberar a la matriz*/
	free(Mat);
}

