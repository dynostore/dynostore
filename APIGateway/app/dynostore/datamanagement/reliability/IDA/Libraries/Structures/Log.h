/** 
 * Estructura que contiene los datos necesarios para editar la bitacora.
 * 
 * FILE *archivo: Contiene el apuntador al archivo deseado
 * int estadoDeapertura: 1 si esta abierto, -1 si no se logro abrir 
 * 		correctamente y 0 si no se a tratado de abrir el archivo. * 
 * */
struct Bitacora {
	FILE *archivo;
	int estadoDeApertura;
};

struct LogFile {
	FILE *file;
	int status;
};
