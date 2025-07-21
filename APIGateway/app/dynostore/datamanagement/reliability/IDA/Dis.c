/*dispersion IDA con cualquier n,m sobre cualquier campo <= 16
El programa se ejecuta de la siguiente manera desde la linea de comandos:
./Dis N M k S006.png
*/
#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <sys/time.h>
#include <string.h>
#include "Libraries/LogAdministrator.h"

struct timeval itStart, itEnd;
struct timeval stStart, stEnd;
int microsegundosInput;
int microsegundosST;

/* VARIABLES GLOBALES */
typedef unsigned int **Matriz;
typedef unsigned int *Vector;
unsigned short int N, M, k, campo, alpha_base;
Matriz MatrizTransf;
Vector Tabla_Alphas;
Vector LAlphas;

/*FUNCIONES*/
void GeneraCampo(unsigned short int k);
void Generar_Alphas(unsigned short int tam_camp);
void GeneraVandermonde(void);
void Dispersa8(char *file);
void Dispersa16(char *file);
Matriz Crear_Matriz(unsigned short int n, unsigned short int m);
Vector Crear_Vector(unsigned int N);
void Imprime_Matriz(Matriz Mat, unsigned short int n, unsigned short int m);
void Imprime_Vector(Vector Arreglo, int n);
void Liberar_Matriz(Matriz Mat, unsigned short int n);

/* OPERACIONES CON CAMPOS */
unsigned int Suma_Resta(unsigned int a, unsigned int b);
unsigned int Multiplicacion(unsigned int a, unsigned int b);

int main(int argc, char **argv)
{

	/*struct LogFile AccessLayerLog;
	char *logPath = "Salida/file1_1MB.csv";
	char logContent[255];

	char headLine[300];
		strcpy(headLine, "");
		if(!fileExists(logPath)){
		sprintf(
			headLine,
			"%s, %s, %s, %s",
				"N","M", "K","ResposeTime\n"
			);
		}*/

	if (argc != 5)
	{
		printf("Escribe: n,m,k,archivo\n");
		exit(1);
	}

	gettimeofday(&itStart, NULL);
	N = atoi(argv[1]);
	M = atoi(argv[2]);
	k = atoi(argv[3]);

	gettimeofday(&itEnd, NULL);

	gettimeofday(&stStart, NULL);
	campo = pow(2, k) - 1;
	GeneraVandermonde();
	GeneraCampo(k);

	switch (k)
	{
	case 8:
		Dispersa8(argv[4]);
		break;
	case 16:
		Dispersa16(argv[4]);
		break;
	default:
		printf("No tengo ese campo %u\n", k);
		exit(1);
	}

	/*Liberar memoria*/
	Liberar_Matriz(MatrizTransf, N);
	free(Tabla_Alphas);
	free(LAlphas);
	Tabla_Alphas = NULL;
	LAlphas = NULL;
	gettimeofday(&stEnd, NULL);

	// Tiempo de respuesta
	// microsegundosInput = ((itEnd.tv_usec - itStart.tv_usec)  + ((itEnd.tv_sec - itStart.tv_sec) * 1000000.0f));
	microsegundosST = ((stEnd.tv_usec - stStart.tv_usec) + ((stEnd.tv_sec - stStart.tv_sec) * 1000000.0f));
	printf("%u\t%u\t%i\n", N, M, microsegundosST / 1000);

	// Se abre el archivo
	/*AccessLayerLog = openLog( logPath );
	if ( AccessLayerLog.status == 1 ){
		writeLine(AccessLayerLog, headLine);
		strcpy(headLine, "");
	} else {
		printf("\t\tError: No se pudo abrir la bitacora\n");
		printf("Verificar que la ruta %s exista", logPath);
	}

	sprintf(logContent, "%s, %i, %i,%i, ", N, M, K, microsegundosST/1000);


	if(AccessLayerLog.status == 1){ 	//Esta abierta
		//Se escribe el registro a la bitacora
		writeLine(AccessLayerLog, logContent);
		closeLog(AccessLayerLog);
	} else { //No se encuentra abierta
		//Se muestra el contenido en la pantalla
		printf("Linea de la bitacora:\n%s",logContent);
	}*/
	return 0;
}
void Dispersa8(char *file)
{
	FILE *in, **Salidas;
	int i, j, regLeidos = -1, regEscritos, bandera = 0;
	unsigned char valor;
	char BufferNombre[10];
	unsigned char *BufferArchivo;
	unsigned int aux = 0, Simbolos = 0;

	if ((BufferArchivo = (char *)malloc(M * sizeof(char))) == NULL)
		printf("No se pudo crear el buffer del archivo");

	Salidas = malloc(N * sizeof(FILE *));
	if (Salidas == NULL)
	{
		printf("No se pudo crear apuntadores a archivo\n");
		return;
	}
	for (i = 0; i < N; i++) // Creación de archivo de cada disperso
	{
		sprintf(BufferNombre, "D%i", i);
		Salidas[i] = fopen(BufferNombre, "wb");
		if (Salidas[i] == NULL)
		{
			printf("No se puede crear los dispersos\n");
			return;
		}
	}
	if ((in = fopen(file, "rb")) == NULL)
	{
		printf("no se puede abrir o el archivo no existe\n");
		exit(1);
	}

	// Procesamiento 2 -service time
	for (i = 0; i < N; i++)
	{
		valor = 48;
		regEscritos = fwrite(&valor, sizeof(char), 1, Salidas[i]);
		valor = N;
		regEscritos = fwrite(&valor, sizeof(char), 1, Salidas[i]);
		valor = M;
		regEscritos = fwrite(&valor, sizeof(char), 1, Salidas[i]);
		for (j = 0; j < M; j++)
		{
			valor = MatrizTransf[i][j];
			regEscritos = fwrite(&valor, sizeof(char), 1, Salidas[i]);
		}
	}

	// Lectura 1 - read time
	do
	{
		for (i = 0; i < M; i++)
			BufferArchivo[i] = 0;
		bandera = 0;
		for (i = 0; i < M && regLeidos != 0; i++)
		{
			valor = 0;
			regLeidos = fread(&valor, sizeof(char), 1, in);
			if (regLeidos != 0)
			{
				BufferArchivo[i] = abs(valor);
				Simbolos++;
				bandera++;
			}
		}

		if (bandera != 0)
		{
			/*GENERAR DISPERSOS*/
			for (i = 0; i < N; i++)
			{
				for (j = 0; j < M; j++)
				{
					aux = Suma_Resta(aux, Multiplicacion(MatrizTransf[i][j], BufferArchivo[j]));
				}
				valor = aux;
				regEscritos = fwrite(&valor, sizeof(char), 1, Salidas[i]);
				aux = 0;
			}
		}
	} while (regLeidos != 0);

	valor = Simbolos % M;
	for (i = 0; i < N; i++)
	{
		rewind(Salidas[i]);
		regEscritos = fwrite(&valor, sizeof(char), 1, Salidas[i]);
	}

	fclose(in);
	for (i = 0; i < N; i++) // Cierre de archivo de cada disperso
		fclose(Salidas[i]);
	free(Salidas);
	free(BufferArchivo);
}

/******************************************************************
	DISPERSION PARA 16 BITS UTILIZANDO unsigned short int
*******************************************************************/
void Dispersa16(char *file)
{
	FILE *in, **Salidas;
	int i, j, regLeidos = -1, regEscritos, bandera = 0;
	unsigned short int valor;
	char BufferNombre[strlen(file) + 10];
	unsigned short int *BufferArchivo;
	unsigned int aux = 0, Simbolos = 0;

	if ((BufferArchivo = (short int *)malloc(M * sizeof(short int))) == NULL)
		printf("No se pudo crear el buffer del archivo");

	Salidas = malloc(N * sizeof(FILE *));
	if (Salidas == NULL)
	{
		printf("No se pudo crear apuntadores a archivo\n");
		return;
	}
	for (i = 0; i < N; i++) // Creación de archivo de cada disperso
	{
		sprintf(BufferNombre, "%sD%i", file,i);
		Salidas[i] = fopen(BufferNombre, "wb");
		if (Salidas[i] == NULL)
		{
			printf("No se puede crear los dispersos\n");
			return;
		}
	}
	if ((in = fopen(file, "rb")) == NULL)
	{
		printf("no se puede abrir o el archivo no existe\n");
		exit(1);
	}
	for (i = 0; i < N; i++)
	{
		valor = 48;
		regEscritos = fwrite(&valor, sizeof(short int), 1, Salidas[i]);
		valor = N;
		regEscritos = fwrite(&valor, sizeof(short int), 1, Salidas[i]);
		valor = M;
		regEscritos = fwrite(&valor, sizeof(short int), 1, Salidas[i]);
		for (j = 0; j < M; j++)
		{
			valor = MatrizTransf[i][j];
			regEscritos = fwrite(&valor, sizeof(short int), 1, Salidas[i]);
		}
	}
	do
	{
		for (i = 0; i < M; i++)
			BufferArchivo[i] = 0;
		bandera = 0;
		for (i = 0; i < M && regLeidos != 0; i++)
		{
			valor = 0;
			regLeidos = fread(&valor, sizeof(char), 2, in);
			if (regLeidos != 0)
			{
				BufferArchivo[i] = abs(valor);
				Simbolos++;
				bandera++;
			}
		}
		if (bandera != 0)
		{
			/*GENERAR DISPERSOS*/
			for (i = 0; i < N; i++)
			{
				for (j = 0; j < M; j++)
				{
					aux = Suma_Resta(aux, Multiplicacion(MatrizTransf[i][j], BufferArchivo[j]));
				}
				valor = aux;
				regEscritos = fwrite(&valor, sizeof(short int), 1, Salidas[i]);
				aux = 0;
			}
		}
	} while (regLeidos != 0);

	valor = Simbolos % M;
	for (i = 0; i < N; i++)
	{
		rewind(Salidas[i]);
		regEscritos = fwrite(&valor, sizeof(short int), 1, Salidas[i]);
	}

	fclose(in);
	for (i = 0; i < N; i++) // Cierre de archivo de cada disperso
		fclose(Salidas[i]);
	free(Salidas);
	free(BufferArchivo);
}

void GeneraVandermonde(void)
{
	int i, j, potencia = 0;
	MatrizTransf = Crear_Matriz(N, M);
	for (i = 0; i < N; i++)
	{
		for (j = 0; j < M; j++)
		{
			MatrizTransf[i][j] = ((unsigned long int)(pow((i + 1), j))) % campo;
		}
	}
}
void GeneraCampo(unsigned short int k)
{
	unsigned int primitivo; // Polinomio primitivo de cada campo

	switch (k)
	{
	case 4:
		primitivo = 19;
		alpha_base = primitivo & (unsigned int)campo; // se calcula con una máscara del alpha de grado máximo del campo
		break;
	case 8:
		primitivo = 369;
		alpha_base = primitivo & (unsigned int)campo;
		break;
	case 16:
		primitivo = 69643;
		alpha_base = primitivo & (unsigned int)campo;
		break;
	}
	Tabla_Alphas = Crear_Vector(campo + 1);
	LAlphas = Crear_Vector(campo + 1);
	Generar_Alphas(k);
}
void Generar_Alphas(unsigned short int tam_camp)
{
	unsigned int indice = 0, x = 0; // Indice es utilizado en ambos vectores
	Tabla_Alphas[indice] = indice;
	LAlphas[indice] = indice;
	for (indice = 0; indice < campo; indice++) // Recorrido de los vectores hasta el tamaño del campo
	{
		if (indice < tam_camp)
		{
			Tabla_Alphas[indice] = (pow(2, indice));
			LAlphas[Tabla_Alphas[indice]] = indice;
		}
		else
		{
			if (indice == tam_camp) // b) si indice==tamaño del campo+1, obten la base del polinomio primitivo y agrega a Tabla_alphas e indexa LAlphas
			{
				x = alpha_base;
				Tabla_Alphas[indice] = alpha_base;
				LAlphas[alpha_base] = indice;
			}
			else // c) si indice>tamaño del campo+1, desplaza a la izq 1 vez( equivalente a multiplicar por 2
			{
				// x=2*x;
				x <<= 1;
				if (x <= campo)
				{ // c.1) si el resultado no excede alpha máximo del campo, dicho de otra manera, no excede el tamaño del campo
					// printf("indice:%i\n",indice);
					Tabla_Alphas[indice] = x; // Entonces, agrega a Tabla_alphas e indexa L_Alphas
					// printf("Pase Tabla_Alphas\n");
					LAlphas[x] = indice;
				}
				else
				{
					x = x & (unsigned int)(campo); // c.2) si el resultado excede el tamaño del campo, se aplica una xor del valor del bit más significativo y agrega a Tabla_alphas e indexa LAlphas
					x = x ^ alpha_base;
					Tabla_Alphas[indice] = x;
					LAlphas[x] = indice;
				}
			}
		}
	}
}
/****************************************************************
				OPERACIONES CON CAMPOS
****************************************************************/
unsigned int Suma_Resta(unsigned int a, unsigned int b)
{
	return (a ^ b);
}

unsigned int Multiplicacion(unsigned int a, unsigned int b)
{
	if ((a != 0) && (b != 0))
	{
		return (Tabla_Alphas[((LAlphas[a] + LAlphas[b]) % (campo))]);
	}
	else
		return (0);
}

Matriz Crear_Matriz(unsigned short int n, unsigned short int m)
{
	int i;
	Matriz A;
	/*reservar memoria para numero de filas de tipo apuntador entero*/
	if ((A = (Matriz)malloc(n * sizeof(unsigned int *))) == NULL)
	{
		printf("No se pudo reservar memoria\n");
		return NULL;
	}
	// Recorrer filas
	for (i = 0; i < n; i++)
	{
		/*reservar memoria para numero de columnas de tipo entero*/
		if ((A[i] = (unsigned int *)malloc(2 * m * sizeof(unsigned int))) == NULL)
			return NULL;
	}
	return A;
}

Vector Crear_Vector(unsigned int N)
{
	Vector A;
	if ((A = (Vector)malloc(N * sizeof(unsigned int))) == NULL)
	{
		printf("No se pudo reservar memoria\n");
		return NULL;
	}
	else
	{
		return A;
	}
}
void Imprime_Matriz(Matriz Mat, unsigned short int n, unsigned short int m)
{
	int i, j;
	for (i = 0; i < n; i++)
	{
		for (j = 0; j < m; j++)
		{
			printf("%i ", Mat[i][j]);
		}
		printf("\n");
	}
	printf("\n");
}
void Imprime_Vector(Vector Arreglo, int n)
{
	int i;
	for (i = 0; i < n; i++)
	{
		printf("%i,", Arreglo[i]);
	}
	printf("\n");
}
void Liberar_Matriz(Matriz Mat, unsigned short int n)
{
	int i;
	/*Recorrer todos los punteros de arreglos y liberar uno por uno*/
	for (i = 0; i < n; i++)
		free(Mat[i]);
	/*Al final liberar a la matriz*/
	free(Mat);
}
