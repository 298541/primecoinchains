#include <stdio.h>
#include <stdlib.h>
#include <string.h>


char* add(char*, char*);
char* subtractone(char*);
void print1CCchain(char*, int);
void print2CCchain(char*, int);
void printTWNchain(char*, int);

//Struct for storing the prime origin data which the program recieved via arguments
struct primeorigin
{
   char* primeorigin;
   char* chaintype;
   int chainlength;
};

//Struct to store primechain data as linked list
struct primechainpart
{
    char* prime;
    int count;
    struct primechain *next;
};

typedef struct primeorigin PRIMEORIGIN;
typedef struct primechainpart* LPPRIMECHAINPART;

int main(int args, char* argv[])
{
    //If arguments don't fit
    if(args != 4 || (strlen(argv[2]) != 5)){
        fprintf(stderr, "Prime Number origin, Prime Type and Chain length needed (has to be correct!)\n");
        fprintf(stderr, "Example: .%s 1063473223700055796187522816436390954029031047508587021137880214019198851841814626410 1CC07 7\n", argv[0]);
        exit(-1);
    }

    //Set prime origin
    PRIMEORIGIN po;
    po.primeorigin = argv[1];

    //Detect type of chain
    char chaintype[4];
    strncpy(chaintype, argv[2], 3);
    chaintype[3] = '\0';
    po.chaintype = chaintype;

    //Detect length of chain
    //char chainlength[3];
    char chainlength[2];
    //strncpy(chainlength, argv[2]+3, 2);
    strcpy(chainlength, argv[3]);
    chainlength[strlen((argv[3]))] = '\0';
    po.chainlength = atoi(chainlength);

    //printf("%s\n", po.primeorigin);
    //printf("%s\n", po.chaintype);
    //printf("%i\n", po.chainlength);

    //add("5", "55555");
    //subtractone("10");

    //Depending on chain type, print prime chain
    if(strcmp("1CC", po.chaintype) == 0){
        print1CCchain(po.primeorigin, po.chainlength);
    }else if(strcmp("2CC", po.chaintype) == 0){
        print2CCchain(po.primeorigin, po.chainlength);
    }else if(strcmp("TWN", po.chaintype) == 0){
        printTWNchain(po.primeorigin, po.chainlength);
    }

    return 0;
}

void print1CCchain(char* primeorigin, int primelength){

    if(primelength == 0){
        return;
    }

    print1CCchain(add(primeorigin, primeorigin), primelength - 1);

    printf("%s,", subtractone(primeorigin));
}

void print2CCchain(char* primeorigin, int primelength){

    if(primelength == 0){
        return;
    }

    print2CCchain(add(primeorigin, primeorigin), primelength - 1);

    printf("%s,", add(primeorigin, "1"));
}

/*
void printTWNchain(char* primeorigin, int primelength){

    if(primelength == 0){
        return;
    }

    printTWNchain(add(primeorigin, primeorigin), primelength - 1);

    printf("%s,%s,", add(primeorigin, "1"), subtractone(primeorigin));
}
*/

void printTWNchain(char* primeorigin, int primelength){

    if(primelength <= 0){
        return;
    }

    printTWNchain(add(primeorigin, primeorigin), primelength - 2);

    if(primelength > 1){
        printf("%s,%s,", add(primeorigin, "1"), subtractone(primeorigin));
    }
    else{
        printf("%s,", subtractone(primeorigin));
    }
}

char* add(char* num1, char* num2){

    //Counter
    int i, j;

    //If one of the "numbers" is null
    if(num1 == NULL || num2 == NULL){
        return NULL;
    }

    //Length of both numbers
    int lennum1 = strlen(num1);
    int lennum2 = strlen(num2);

    //Hook - length of bigger number
    int hook = 0;
    if(lennum1 >= lennum2){
        hook = lennum1;
    }else{
        hook = lennum2;
    }

    //Allocate space for calculated number
    char* newnum = calloc(hook + 2, sizeof(char));

    //Noted if calculation surpasses 9
    int noted = 0;
    //Temp safes current value
    int temp = 0;
    //Is counted j++ for every round in the for loop and subtracted from lennumX to go through the digits from right to left
    j = 0;

    int currnum1 = 0;
    int currnum2 = 0;

    for(i = hook; i >= 0; i--){

        //If lennumX is smaller than j, we are out of bound with this number
        if((lennum1 - j) < 0 ){
            currnum1 = 0;
        }else{
            currnum1 = (int) num1[lennum1 - j - 1] - 48;
        }

        if((lennum2 - j) < 0 ){
            currnum2 = 0;
        }else{
            currnum2 = (int) num2[lennum2 - j - 1] - 48;
        }

        //if currentnumX is smaller than 0 - impossible for char - set to 0;
        if(currnum1 < 0){
            currnum1 = 0;
        }
        if(currnum2 < 0){
            currnum2 = 0;
        }

        //calculate and create char value
        temp = (currnum1 + currnum2) + 48;

        //if overhead from previous calculation, add
        if(noted == 1){
            temp++;
        }
        //If the number is bigger than 9 (ASCII 57)
        if(temp > 57){
            //note overhead
            noted = 1;
            //substract 10
            temp = temp - 10;
        }else{//if it is not bigger
            noted = 0;
        }

        *(newnum+i) = (char) temp;
        j++;
    }

    newnum[hook+1] = '\0';

    //Determine how many zeros are at the beginning
    int zerooffset = 0;
    while(newnum[zerooffset] == '0'){
        zerooffset++;
    }

    //Shift char array to by zerooffset positions
    if(zerooffset > 0){
        int l;
        for(l = 0; l < strlen(newnum); l++){
            newnum[l] = newnum[l + zerooffset];
        }
    }

    return newnum;

}

char* subtractone(char* num1){

    //Counter
    int i, j;

    //If one of the "numbers" is null
    if(num1 == NULL){
        return NULL;
    }

    //Length of both numbers
    int lennum1 = strlen(num1);

    //Hook - length of number
    int hook = lennum1;

    //Allocate space for calculated number
    char* newnum = calloc(hook + 1, sizeof(char));

    //Noted if calculation goes below 0
    int noted = 0;
    //Temp safes current value
    int temp = 0;
    //Is counted j++ for every round in the for loop and subtracted from lennumX to go through the digits from right to left
    j = 0;

    int currnum1 = 0;

    for(i = hook; i >= 0; i--){

        //If lennumX is smaller than j, we are out of bound with this number
        if((lennum1 - j) < 0 ){
            currnum1 = 0;
        }else{
            currnum1 = (int) num1[lennum1 - j - 1] - 48;
        }

        //if currentnumX is smaller than 0 - impossible for char - set to 0;
        if(currnum1 < 0){
            currnum1 = 0;
        }

        //if its the first round, decrease by one
        if(i == hook){
            temp = (currnum1 - 1) + 48;
        }else{
            temp = currnum1 + 48;
        }

        //if overhead from previous calculation, add
        if(noted == 1){
            temp--;
        }
        //If the number is smaller than 0 (ASCII 48)
        if(temp < 48){
            //note overhead
            noted = 1;
            //add 10
            temp = temp + 10;
        }else{//if it is not smaller
            noted = 0;
        }

        *(newnum+i) = (char) temp;
        j++;
    }

    newnum[hook+1] = '\0';

    //Determine how many zeros are at the beginning
    int zerooffset = 0;
    while(newnum[zerooffset] == '0'){
        zerooffset++;
    }

    //Shift char array to by zerooffset positions
    if(zerooffset > 0){
        int l;
        for(l = 0; l < strlen(newnum); l++){
            newnum[l] = newnum[l + zerooffset];
        }
    }

    return newnum;
}
