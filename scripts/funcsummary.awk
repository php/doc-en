/^[[:space:]]*\/\*[[:space:]]*\{\{\{[[:space:]]*proto/ { 
	split($0,proto,"proto[[:space:]]+|\\*/[[:space:]]*$");
	parse=1; 
	same=1;
	lc=0;
}
/\*\// {
	if(parse) {
		lines="";
		for(i=0;i<lc;i++) {
			lines = sprintf("%s %s ",lines,line[i]);
		}
		if(!same) {
			split($0,temp,"\\*/[[:space:]]*$");
			lines = sprintf("%s %s ",lines,temp[1]);
		}
		printf("%s --- %s\n",proto[2],lines);
		parse=0;
	}
	next;
}
{	
	if(parse && !same) { 
		split($0,temp,"\\*/[[:space:]]*$");
		line[lc++]=temp[1];
		
	} 
	same=0;
}
