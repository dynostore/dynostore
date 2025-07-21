/**
 * Contiene los elementos necesarios para la apertura, escritura y 
 * cierre del archivo que será utilizado de bitacora
 * */

#include <stdio.h>
#include "Structures/Log.h"

/*Declaracion de funciones*/
FILE *abrirB ( char * pathBitacora );
int comprobarAbertura ( FILE *bitacora );
struct Bitacora abrirBitacora ( char *rutaBitacora );
void escribirRegistro ( struct Bitacora bitacoraAbierta, 
						char *registro );
void cerrarBitacora ( struct Bitacora bitacoraAbierta );



/**
 * Crea el archivo solicitado o lo abre si este existe en su ultima 
 * posicion para poder agregar más contenido.
 * 
 * @param char *pathBitacora: Es la ruta en la cual se quiere generar/
 * 		abrir la bitacora.
 * @return File *bitacora: Si se logra abrir el archivo retorna el 
 * 		puntero al archivo abierto, un puntero con valor NULL de lo
 * 		contrario.
 * */
FILE *abrirB ( char *pathBitacora ) {
	FILE *bitacora;
	bitacora = fopen ( pathBitacora, "a" );
	return bitacora;
}

/**
 * Comprueba el estado de apertura o creacion del archivo ingresado
 * @param FILE *bitacora: Es el puntero al archivo abierto
 * @return -1 Si el contenido de la *bitacora es NULL, 1 de lo contrario
 * */
int comprobarAbertura ( FILE *bitacora ) {
	if ( bitacora == NULL ) {
		return -1;
	}
	else {
		return 1;
	}
}

/**
 * Crea el archivo solicitado o lo abre si este existe
 * @param *rutaBitacora path del archivo que se desea abrir/crear
 * @return struct Bitacora: Es la representacion de nuestro archivo
 * */
struct Bitacora abrirBitacora ( char *rutaBitacora ) {
	struct Bitacora resultadoApertura;
	resultadoApertura.archivo = abrirB ( rutaBitacora );
	resultadoApertura.estadoDeApertura = comprobarAbertura ( 
		resultadoApertura.archivo
	);
	return resultadoApertura;	
}

/**
 * Inserta una linea al final del texto abierto.
 * 
 * @param struct Bitacora: Es la representacion del archivo abierto
 * @param char *registro: Es el contenido que se le desea adicionar al
 * archivo.
 * */
void escribirRegistro ( struct Bitacora bitacoraAbierta, char *registro 
) {
	fprintf ( bitacoraAbierta.archivo, "%s", registro );
}

/** 
 * Cierra el archivo utilizado para la bitacora
 * 
 * @param struct Bitacora: Es la representacion del archivo abierto
 * */
void cerrarBitacora ( struct Bitacora bitacoraAbierta ){
	fclose ( bitacoraAbierta.archivo );
}
 
/** 
 * Ejemplo de utilizacion de las funciones
 * */
//int main ( int argc, char **argv ) {
 	
 	//struct Bitacora miBitacora;
 	
 	//miBitacora = abrirBitacora ( "Segmentador.txt" );
 	
 	//if ( miBitacora.estadoDeApertura == 1 ) {
		//for ( int i = 0; i < 10; i++ ) {
			//char msj[255];
			//sprintf ( msj, "Hola: %i\n", i );
			//escribirRegistro ( miBitacora, msj );
		//}
		//cerrarBitacora ( miBitacora );
	//}
	//else{
		//printf ( "¡ERROR No se pudo abrir la bitacora!" );
	//}
 	
 	//return 0;
//}
