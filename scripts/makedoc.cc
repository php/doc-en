/**********************************************************************
 * Rick,
 *
 * I made some more changes to fix another portability problem.  It seems
 * that SOME compilers will pad a structure to a DWORD boundary when you
 * use the sizeof operator.  In particular, for the Solaris compiler, the
 * 78 byte tDocHeader structure is reported as having 80 bytes.  This shifts
 * EVERYTHING by two bytes and wreaks havoc in the generated .prc file.
 * I fixed this (look at the comments in struct tDocHeader and the DOCHEADSZ
 * definition) in the two places it occurred.
 *
 * I also fixed a spelling error in an error message.
 *
 * I also changed the usage message to say this is version 0.7a (rather than
 * 0.6).
 *
 * I also changed the return type of main() to be int and added various
 * calls to exit() as needed.  Needed for portability and correctness.
 *
 * -- Harold Bamford
 **********************************************************************/
// MakeDoc
// version 0.7a
//
// Compresses text files into a format that is ready to export to a Pilot
// and work with Rick Bram's PilotDOC reader.
//
// Freeware
//
// ver 0.6   enforce 31 char limit on database names
// ver 0.7   change header and record0 to structs
// ver 0.7a  minor mispellings and portability issues

#define UNIX 1
#ifdef sparc
#	ifndef UNIX
#	define UNIX 1
#	endif
#endif

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

//template<class A> A max(const A& a, const A& b) {return (a<b) ? b : a;}
#define max(a,b) ((a>b) ? a : b)

typedef unsigned char byte;
typedef unsigned long DWORD;
typedef unsigned short WORD;
#define DISP_BITS 11
#define COUNT_BITS 3

// all numbers in these structs are big-endian, MAC format
struct tDocHeader {
    char sName[32];		// 32 bytes
    DWORD dwUnknown1;	// 36
    DWORD dwTime1;		// 40
    DWORD dwTime2;		// 44
    DWORD dwTime3;		// 48
    DWORD dwLastSync;	// 52
    DWORD ofsSort;		// 56
    DWORD ofsCatagories;	// 60
    DWORD dwCreator;	// 64
    DWORD dwType;		// 68
    DWORD dwUnknown2;	// 72
    DWORD dwUnknown3;	// 76
    WORD  wNumRecs;		// 78
};

// Some compilers pad structures out to DWORD boundaries so using sizeof()
// doesn't give the intended result.
#define DOCHEADSZ 78

struct tDocRecord0 {
    WORD wVersion;		// 1=plain text, 2=compressed
    WORD wSpare;
    DWORD dwStoryLen;	// in bytes, when decompressed
    WORD wNumRecs; 		// text records only; equals tDocHeader.wNumRecs-1
    WORD wRecSize;		// usually 0x1000
    DWORD dwSpare2;
};

////////////// utilities //////////////////////////////////////

WORD SwapWord21(WORD r)
{
    return (r>>8) + (r<<8);
}
WORD SwapWord12(WORD r)
{
    return r;  
}
DWORD SwapLong4321(DWORD r)
{
    return  ((r>>24) & 0xFF) + (r<<24) + ((r>>8) & 0xFF00) + ((r<<8) & 0xFF0000);
}
DWORD SwapLong1234(DWORD r)
{
    return r;
}

WORD (*SwapWord)(WORD r) = NULL;
DWORD (*SwapLong)(DWORD r) = NULL;

// copy bytes into a word and double word and see how they fall,
// then choose the appropriate swappers to make things come out
// in the right order.
int SwapChoose()
{
    union { char b[2]; WORD w; } w;
    union { char b[4]; DWORD d; } d;

    strncpy(w.b, "\1\2", 2);
    strncpy(d.b, "\1\2\3\4", 4);

    if (w.w == 0x0201)
	SwapWord = SwapWord21;
    else if (w.w == 0x0102)
	SwapWord = SwapWord12;
    else
	return 0;

    if (d.d == 0x04030201)
	SwapLong = SwapLong4321;
    else if (d.d == 0x01020304)
	SwapLong = SwapLong1234;
    else
	return 0;
  
    return 1;
}  

// replacement for strstr() which deals with 0's in the data
byte* memfind(byte* t, int t_len, byte* m, int m_len)
{
    int i;

    for (i = t_len - m_len + 1 ; i>0; i--, t++)
	if (t[0]==m[0] && memcmp(t,m,m_len)==0)
	    return t;
    return 0;
}


/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
/////////////////////                                  //////////////////////
/////////////////////      tBuf class                  //////////////////////
/////////////////////                                  //////////////////////
/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////


struct tBuf {
    byte* buf;
    unsigned len;

    tBuf() {buf = new byte[len = 6000];};
    ~tBuf() {	if (buf) delete[] buf; }
    unsigned Len() const {	return len;	}

    unsigned RemoveBinary();
    unsigned Decompress();
    unsigned Compress();
    unsigned Issue(byte src, int& bSpace);
    unsigned DuplicateCR();
    void Clear() {delete[] buf; buf = new byte[len = 6000]; }
    void Dump() {printf("\nbuffer len=%d",len);}
};



//
// Issue()
//
// action: handle the details of writing a single
//		character to the compressed stream
//
unsigned
tBuf::Issue(byte src, int& bSpace)
{
    int iDest = len;
    byte* dest = buf;

    // if there is an outstanding space char, see if
    // we can squeeze it in with an ASCII char
    if (bSpace)
    {
	if (src>=0x40 && src<=0x7F)
	    dest[iDest++] = src ^ 0x80;
	else
	{
	    // couldn't squeeze it in, so issue the space char by itself
	    // most chars go out simple, except the range 1...8,0x80...0xFF
	    dest[iDest++] = ' ';
	    if (src<0x80 && (src==0 || src>8) )
		dest[iDest++] = src;
	    else
		dest[iDest++] = 1, dest[iDest++] = src;
	}
	// knock down the space flag
	bSpace = 0;
    }
    else
    {
	// check for a space char
	if (src==' ')
	    bSpace = 1;
	else
	{
	    if (src<0x80 && (src==0 || src>8))
		dest[iDest++] = src;
	    else
		dest[iDest++] = 1, dest[iDest++] = src;

	}
    }
    len = iDest;
    return iDest;
}

//
// Compress
//
// params: 	none
//
// action:	takes the given buffer,
//					and compresses
//					the original data down into a second buffer
//
// comment:	This version make heavy use of walking pointers.
//
unsigned tBuf::Compress()
{
    int i,j;
    int bSpace = 0;

    // run through the input buffer
    byte* pBuffer;		// points to the input buffer
    byte* pHit;		// points to a walking test hit; works upwards on successive matches
    byte* pPrevHit;		// previous value of pHit
    byte* pTestHead;	// current test string
    byte* pTestTail;	// current walking pointer; one past the current test buffer
    byte* pEnd;		// 1 past the end of the input buffer

    pHit = pPrevHit = pTestHead = pBuffer = buf;
    pTestTail = pTestHead+1;
    pEnd = buf + len;
//printf("pointers %x %x",pTestTail, pEnd);
//printf("\nstart compression buf len=%d",len);

    // make a dest buffer and reassign the local buffer
    buf = new byte[6000];
    len = 0;		// used to walk through the output buffer

    // loop, absorbing one more char from the input buffer on each pass
    for (; pTestHead != pEnd; pTestTail++)
    {
//printf("\npointers pTestHead %x pTestTail %x pTestHead[]=%x %x",pTestHead, pTestTail, pTestHead[0], pTestHead[1]);
	// establish where the scan can begin
	if (pTestHead - pPrevHit > ((1<<DISP_BITS)-1))
	    pPrevHit = pTestHead - ((1<<DISP_BITS)-1);

	// scan in the previous data for a match
	pHit = memfind(pPrevHit, pTestTail - pPrevHit, pTestHead, pTestTail - pTestHead);

	if (pHit==0)
	    printf("!! bug source %x%x%x, dest %x%x%x, %d bytes",	pPrevHit[0],
		   pPrevHit[1],pPrevHit[2],pTestHead[0],
		   pTestHead[1],	pTestHead[2],	pTestTail-pTestHead);

	// on a mismatch or end of buffer, issued codes
	if (pHit==0
	    || pHit==pTestHead
	    || pTestTail-pTestHead>(1<<COUNT_BITS)+2
	    || pTestTail==pEnd)
	{
	    // issued the codes
	    // first, check for short runs
	    if (pTestTail-pTestHead < 4)
	    {
//printf("\nissue a char %x",pTestHead[0]);
		Issue(pTestHead[0], bSpace);
		pTestHead++;
	    }
	    // for longer runs, issue a run-code
	    else
	    {
		// issue space char if required
		if (bSpace) buf[len++] = ' ', bSpace = 0;

		unsigned int dist = pTestHead - pPrevHit;
		unsigned int compound = (dist << COUNT_BITS) + pTestTail-pTestHead - 4;

		if (dist>=(1<<DISP_BITS)) printf("\n!! error dist overflow");
		if (pTestTail-pTestHead-4>7) printf("\n!! error dist overflow");

		buf[len++] = 0x80 + (compound>>8);
		buf[len++] = compound & 0xFF;
//printf("\nissuing code for sequence len %d <%c%c%c>",pTestTail-pTestHead-1,pTestHead[0],pTestHead[1],pTestHead[2]);
//printf("\n          <%x%x>",pOut[-2],pOut[-1]);
		// and start again
		pTestHead = pTestTail-1;
	    }
	    // start the search again
	    pPrevHit = pBuffer;
	}
	// got a match
	else
	{
	    pPrevHit = pHit;
	}
//printf("pointers %x %x %x",pTestHead, pTestTail, pPrevHit);
	// when we get to the end of the buffer, don't inc past the end
	// this forces the residue chars out one at a time
	if (pTestTail==pEnd) pTestTail--;
    }

    // clean up any dangling spaces
    if (bSpace) buf[len++] = ' ';


    // final scan to merge consecutive high chars together
    int k;
    for (i=k=0; i<len; i++,k++)
    {
	buf[k] = buf[i];
	// skip the run-length codes
	if (buf[k]>=0x80 && buf[k]<0xC0)
	    buf[++k] = buf[++i];
	// if we hit a high char marker, look ahead for another
	else if (buf[k]==1)
	{
	    buf[k+1] = buf[i+1];
	    while (i+2<len && buf[i+2]==1 && buf[k]<8)
	    {
		buf[k]++;
		buf[k+buf[k]] = buf[i+3];
		i+=2;
	    }
	    k += buf[k]; i++;
	}
    }

    // delete original buffer
    delete[] pBuffer;
    len = k;

    return k;
}
/*
	Decompress

	params:	none

	action: make a new buffer
					run through the source data
					check the 4 cases:
						0,9...7F represent self
						1...8		escape n chars
						80...bf reference earlier run
						c0...ff	space+ASCII

*/
unsigned
tBuf::Decompress()
{
    // we "know" that all decompresses fit within 4096, right?
    byte* pOut = new byte[6000];
    byte* in_buf = buf;
    byte* out_buf = pOut;

    int i,j;
    for (j=i=0; j<len; )
    {
	unsigned int c;

	// take a char from the input buffer
	c = in_buf[j++];

	// separate the char into zones: 0, 1...8, 9...0x7F, 0x80...0xBF, 0xC0...0xFF

	// codes 1...8 mean copy that many bytes; for accented chars & binary
	if (c>0 && c<9)
	    while(c--) out_buf[i++] = in_buf[j++];

	// codes 0, 9...0x7F represent themselves
	else if (c<0x80)
	    out_buf[i++] = c;

	// codes 0xC0...0xFF represent "space + ascii char"
	else if (c>=0xC0)
	    out_buf[i++] = ' ', out_buf[i++] = c ^ 0x80;

	// codes 0x80...0xBf represent sequences
	else
	{
	    int m,n;
	    c <<= 8;
	    c += in_buf[j++];
	    m = (c & 0x3FFF) >> COUNT_BITS;
	    n = c & ((1<<COUNT_BITS) - 1);
	    n += 3;
	    while (n--)
	    {
		out_buf[i] = out_buf[i-m];
		i++;
	    }
	}
    }
    delete[] buf;
    buf = pOut;
    len = i;

    return i;
}

unsigned tBuf::DuplicateCR()
{
    byte* pBuf = new byte[2*len];

    int k,j;
    for (j=k=0; j<len; j++, k++)
    {
	pBuf[k] = buf[j];
	if (pBuf[k]==0x0A) pBuf[k++] = 0x0D, pBuf[k] = 0x0A;
    }
    delete[] buf;
    buf = pBuf;
    len = k;
    return k;
}


void Decomp(char* src, char* dest, int bBinary)
{
    FILE* fin;
    FILE* fout;
    fin = fopen(src,"rb");

    if (fin==0)
    {
	printf("problem opening source file %s", src);
	exit(2);
    }

    // just holds the first few bytes of the file
    byte buf[0x100];
    tDocHeader head;

    fread(&head, 1, DOCHEADSZ, fin);
    if (strncmp((char *)&head.dwType, "REAd", 4) != 0
	|| strncmp((char *)&head.dwCreator, "TEXt", 4) != 0)
    {
	//printf("file contains %.4s, %.4s", (char *)&head.dwCreator, (char *)&head.dwType);
	printf(".prc file is not the correct format");
	exit(3);
    }

    WORD bCompressed;
    DWORD dwPos;
    tDocRecord0 rec0;
    // point to start of index
    fseek(fin, 0x4E, SEEK_SET);
    // read the location of the first record
    fread(&dwPos, 4, 1, fin);
    dwPos = SwapLong(dwPos);
    fseek(fin, dwPos, SEEK_SET);
    fread(&rec0, sizeof(rec0), 1, fin);
    bCompressed = SwapWord(rec0.wVersion);
    if (bCompressed!=1 && bCompressed!=2)
	printf("\nWARNING: unknown file compression type:%d",bCompressed);
    bCompressed--;

    fout = fopen(dest,"wb");
    if (fout==0)
    {
	printf("problem opening output file %s",dest);
	exit(2);
    }


    DWORD dwLen;
    fseek(fin,0,SEEK_END);
    dwLen = ftell(fin);

    WORD nRecs;
    nRecs = SwapWord(head.wNumRecs) - 1;

    // this is the main record buffer
    // it knows how to stretch to accomodate the decompress
    tBuf t;

    DWORD dwRecLen;
    for (int i=0; i<nRecs; i++)
    {
	// read the record offset
	fseek(fin, 0x56 + 8*i, SEEK_SET);
	fread(&dwPos, 4, 1, fin);
	dwPos = SwapLong(dwPos);

	// read start of next record
	fseek(fin, 0x5E + 8*i, SEEK_SET);
	fread(&dwRecLen, 4, 1, fin);
	dwRecLen = SwapLong(dwRecLen);

	// for the last, use the file len
	if (i==nRecs-1) dwRecLen = dwLen;

	dwRecLen -= dwPos;

	fseek(fin,dwPos,SEEK_SET);
	int n = fread(t.buf, 1, dwRecLen, fin);
	t.len = n;
	if(bCompressed)
	    t.Decompress();

	// check for CR insert
	if (!bBinary)
	    t.DuplicateCR();
	printf("\rreconverting %s: record %d of %d\n",head.sName,i,nRecs);

	fwrite(t.buf, 1, t.Len(), fout);
    }

    fclose(fin);
    fclose(fout);

}

// this nasty little beast removes really low ASCII and 0's
// and handles the CR problem
//
// if a cr appears before a lf, then remove the cr
// if a cr appears in isolation, change to a lf
unsigned tBuf::RemoveBinary()
{
    byte* in_buf = buf;
    byte* out_buf = new byte[len];

    int k,j;
    for (j=k=0; j<len; j++,k++)
    {
	// copy each byte
	out_buf[k] = in_buf[j];

	// throw away really low ASCII
	if (out_buf[k] < 9)
	    k--;

	// for CR
	if (out_buf[k]==0x0D)
	{
	    // if next is LF, then drop it
	    if (j<len-1 && in_buf[j+1]==0x0A)
		k--;
	    else // turn it into a LF
		out_buf[k] = 0x0A;
	}
    }
    delete[] buf;
    buf = out_buf;
    len = k;
    return k;
}

void out_word(short w, FILE* fout)
{
    short m = SwapWord(w);
    fwrite(&m,2,1,fout);
}
void out_long(long d, FILE* fout)
{
    long d1 = SwapLong(d);
    fwrite(&d1,4,1,fout);
}


int
main(int argc, char** argv)
{
    printf("MakeDoc ver 0.7a\n");
    if (argc<4)
    {
	printf("\nsyntax makedoc [-n] [-b] <text-file> <prc-file> <story-name>");
	printf("\n                 convert text files to .PRC format");
	printf("\n       makedoc -d [-b] <prc-file> <text-file>");
	printf("\n                 decodes the PRC back into the txt file");
	printf("\n       -n builds the .prc file without compression");
	printf("\n       -b option compresses/decompresses binary");
#if UNIX
	printf("\n");
#endif
	exit(1);
    }

    int iArg = 1;
    int bDecomp = 0;
    int bBinary = 0;
    int bReport = 0;
    int bCompress = 1;

    if ( ! SwapChoose()) {
	printf("\nfailed to select proper byte swapping algorithm");
#if UNIX
	printf("\n");
#endif
	exit(1);
    }

    while (argv[iArg][0]=='-' || argv[iArg][0]=='\\')
    {
	if (argv[iArg][1]=='d')
	    bDecomp = 1;
	if (argv[iArg][1]=='b')
	    bBinary = 1;
	if (argv[iArg][1]=='r')
	    bReport = 1;
	if (argv[iArg][1]=='n')
	    bCompress = 0;
	iArg++;
    }

    if (bDecomp)
	Decomp(argv[iArg], argv[iArg+1], bBinary);

    else
    {
	FILE* fin;
	FILE* fout;
	tDocHeader head1;

	fin = fopen(argv[iArg],"rb");
	fout = fopen(argv[iArg+1],"wb");
	if (fin==0 || fout==0)
	{
	    printf("problem opening files");
	    exit(2);
	}

	fseek(fin,0,SEEK_END);
	DWORD storySize = ftell(fin);
	fseek(fin,0,SEEK_SET);

	DWORD	x;
	WORD w;
	long	recSize = 4096;
	DWORD		z,numRecs;

	sprintf(head1.sName,"%.31s",argv[iArg+2]);
	head1.sName[31] = 0;
	printf("saving to %s as <%s>,%s%s compressed",argv[iArg+1],argv[iArg+2],
	       bBinary ? " binary mode," : "",
	       bCompress ? "" : " not");

	/*LocalWrite just writes to the new file the number of bytes starting at the passed pointer*/

	head1.dwUnknown1 = 0;
	strncpy((char *)&head1.dwTime1, "\x06\xD1\x44\xAE", 4);
	strncpy((char *)&head1.dwTime2, "\x06\xD1\x44\xAE", 4);
	head1.dwTime3 = 0;
	head1.dwLastSync = 0;
	head1.ofsSort = 0;
	head1.ofsCatagories = 0;
	strncpy((char *)&head1.dwCreator, "TEXt", 4);	// database creator
	strncpy((char *)&head1.dwType, "REAd", 4);	// database type
	head1.dwUnknown2 = 0;
	head1.dwUnknown3 = 0;



	z = (int) (storySize/(long) recSize);
	if (((long) z * recSize) < storySize)
	    z ++;

	numRecs = z;
	z ++;

	head1.wNumRecs = SwapWord(z);		//  the number of records to follow
	fwrite(&head1,1,DOCHEADSZ,fout);
	unsigned long index;
	index = 0x406F8000;		// the pattern for attributes=dirty + unique_id=0x6f8000
	x = 0x50L + (long) z * 8;

	out_long(x,fout);		// start writing the record offsets
	out_long(index,fout);
	x += 0x0010L;

	index++;
	z--;

	while(z--) {
	    out_long(x,fout);		//more record offsets
	    out_long(index++,fout);		// the attributes + ID's
	    x += 0x1000L;
	}
	// one more word.....
	out_word(0,fout);


	tDocRecord0 rec0;
	rec0.wVersion = SwapWord(bCompress ? 2 : 1);
	rec0.wSpare = 0;
	rec0.dwStoryLen = SwapLong(storySize);
	rec0.wNumRecs = SwapWord(SwapWord(head1.wNumRecs) - 1);
	rec0.wRecSize = SwapWord(recSize);
	rec0.dwSpare2 = 0;

	fwrite(&rec0,1,sizeof(rec0),fout);

	int n = recSize;
	// dump the whole story into the new file
	int recNum = 0;
	printf("\n");

	tBuf buf;

	while(recNum < numRecs)
	{
	    long pos;
	    pos = ftell(fout);
	    fseek(fout, 0x56 + 8*recNum, SEEK_SET);
	    if (recNum!=numRecs) out_long(pos,fout);
	    fseek(fout, pos, SEEK_SET);

	    int nOrg;

	    buf.Clear();
	    nOrg = n = fread(buf.buf,1,4096,fin);
	    buf.len = n;
	    if (n==0) break;

	    if (!bBinary)
		buf.RemoveBinary();
	    if (bCompress)
		buf.Compress();
	    n = fwrite(buf.buf,1,buf.Len(),fout);

	    //printf("\rconverting record %d of %d",recNum+1,numRecs);
	    fflush(stdout);
	    if (bReport && n && bCompress)
		printf("\noriginal %d bytes, compressed to %d bytes, ratio: %f5.1\n",
		       nOrg, n, 100. * n / nOrg);
	    recNum++;
	}

	printf("\n");
	fclose(fin);
	fclose(fout);
    }
    exit(0);
}
