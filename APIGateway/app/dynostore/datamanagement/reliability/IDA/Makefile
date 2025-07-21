CC:= gcc
MATH:= -lm
CFLAGS:= -Wall 
RM:= rm 

all:  Dis Rec

Dis: Dis.c
	$(CC)  -o Dis Dis.c $(MATH)


Rec: Rec.c 
	$(CC)  -o Rec Rec.c $(MATH)

clean:
	$(RM) Dis
	$(RM) Rec
