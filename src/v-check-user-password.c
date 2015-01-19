/***************************************************************************/
/*  v_check_user_password.c                                                */
/*                                                                         */
/*  This program compare user pasword from input with /etc/shadow          */
/*  To compile run:                                                        */
/*  "gcc v-check-user-password.c -o v-check-user-password -lcrypt"         */
/*                                                                         */
/*  Thanks to: bogolt, richie and burus                                    */
/*                                                                         */
/***************************************************************************/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/types.h>
#include <pwd.h>
#include <shadow.h>
#include <time.h>
#include <string.h>


int main (int argc, char** argv) {
    /* define ip */
    char *ip = "127.0.0.1";

    /* check argument list */
    if (3 > argc) {
        printf("Error: bad args\n",argv[0]);
        printf("Usage: %s user password [ip]\n",argv[0]);
        exit(1);
    };

    /* check ip */
    if (4 <= argc) {
      ip = (char*)malloc(strlen(argv[3]));
      strcpy(ip, argv[3]);
    }

    /* format current time */
    time_t lt = time(NULL);
    struct tm* ptr = localtime(&lt);
    char str[280];
    strftime(str, 100, "%Y-%m-%d %H:%M:%S ", ptr);

    /* open log file */
    FILE* pFile = fopen ("/usr/local/vesta/log/auth.log","a+");
    if (NULL == pFile) {
        printf("Error: can not open file /usr/local/vesta/log/auth.log \n");
        exit(12);
    }

    int len = 0;
    if(strlen(argv[1]) >= 100) {
        printf("Too long username\n");
        exit(1);
    }

    /* parse user argument */
    struct passwd* userinfo = getpwnam(argv[1]);
    if (NULL != userinfo) {
        struct spwd* passw = getspnam(userinfo->pw_name);
        if (NULL != passw) {
            char* cryptedPasswrd = (char*)crypt(argv[2], passw->sp_pwdp);
            if (strcmp(passw->sp_pwdp,crypt(argv[2],passw->sp_pwdp))==0) {
                /* concatinate time with user and ip */
                strcat(str, userinfo->pw_name);
                strcat(str, " ");
                strcat(str, ip);
                strcat(str, " successfully logged in \n");
                fputs (str,pFile);      /* write */
                fclose (pFile);         /* close */
                exit(EXIT_SUCCESS);     /* exit */
            } else {
                /* concatinate time with user string */
                printf ("Error: password missmatch\n");
                strcat(str, userinfo->pw_name);
                strcat(str, " ");
                strcat(str, ip);
                strcat(str, " failed to login \n");
                fputs (str,pFile);      /* write */
                fclose (pFile);         /* close */
                exit(9);                /* exit */
            };
        }
    } else {
        printf("Error: no such user\n",argv[1]);
        strcat(str, argv[1]);
        strcat(str, " ");
        strcat(str, ip);
        strcat(str, " failed to login \n");
        fputs (str,pFile);      /* write */
        fclose (pFile);         /* close */
        exit(3);
    };

    return EXIT_SUCCESS;
};
