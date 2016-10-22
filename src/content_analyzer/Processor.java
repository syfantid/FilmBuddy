package content_analyzer;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

/**
 * Class to process text (tweets and comments)
 * Created by Antigoni Founta on 13/4/2016.
 */

public class Processor {

    private static ArrayList<String> stopwordsList;
    private static StanfordLemmatizer lemmatizer;

    static {
        stopwordsList = new ArrayList<>();
        lemmatizer =  new StanfordLemmatizer();
        try {
            try(BufferedReader br = new BufferedReader(new FileReader("input/stopwords.txt"))) {
                String line = br.readLine();

                while (line != null) {
                    stopwordsList.add(line);
                    line = br.readLine();
                }
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    /**
     * Lemmatizes the input text
     * @param input The text to be lemmatized
     * @return The lemmatized words
     */
    private static List<String> lemmatize(String input) {
        return lemmatizer.lemmatize(input);
    }

    /**
     * Processes a string
     * @param inputText The string before the processing
     * @return The parsed string
     */
    public static String preprocess(String inputText){
        List<String> input = prepareText(inputText);
        //String[] temp = tokenizer(inputText);
        if(input.size() < 10) { // There is not extended plot technically
            return "";
        }
        String output = "";
        for(String s : input){
            if(isWhitelisted(s) && !s.isEmpty()){
                output = output + " ";
                output = output + s;
            }
        }
        return output;
    }


    /**
     * Method checking if a string is white-listed
     * A string is called white-listed if it's not a stop-word or a number or a URL
     * @param str The string to be checked
     * @return True if the string is white-listed, false otherwise
     */
    private static boolean isWhitelisted(String str){
        return !isStopWord(str) && !isNumeric(str);
    }

    /**
     * Check whether a string contains numeric characters or not
     * @param str The string to be checked
     * @return True, if the string contains numeric characters, or false otherwise
     */
    private static boolean isNumeric(String str){
        return str.matches(".*\\d.*");
    }

    /**
     * Check whether a string contains stop-words or not
     * @param str The string to be checked
     * @return True, if the string contains stop-words, or false otherwise
     */
    public static boolean isStopWord(String str){
        return stopwordsList.contains(str);
    }

    /**
     * Separates string into words based on whitespaces
     * @param input The string to be tokenized
     * @return An array of the strings' words
     */
    private static String[] tokenizer(String input){
        return input.split(" ");
    }

    /**
     * Converts all characters to lower case
     * @param input The string to be turned into lower case
     * @return The string converted to lower case
     */
    private static String toLowerCase(String input){
        return input.toLowerCase();
    }

    /**
     * Removes all punctuation
     * @param input The string to be stripped from punctuation
     * @return The string without punctuation
     */
    private static String removePunctuation(String input){
        return input.replaceAll("\\W", " ");
    }

    /**
     * Removes all single characters from a string
     * @param input The string to be edited
     * @return The string without punctuation
     */
    private static String removeSingleCharacter(String input){
        return input.replaceAll("\\b[a-z]\\b", " ");
    }

    /**
     * Removes comments inside brackets from plot
     * @param input The string to be processed
     * @return The text without the comments
     */
    private static String removeComments(String input) {
        return input.replaceAll("\\{{2}[^\\}]*\\}{2}"," ");
    }

    /**
     * Removes references from plot
     * @param input The string to be processed
     * @return The text without the references
     */
    private static String removeReferences(String input) {
        return input.replaceAll("<\\/ref>|<ref .[^{}]*\\/>"," ");
    }

    /**
     * Removes useless characters like punctuation and single characters and transforms to lowercase
     * @param input The string to be checked
     * @return The string finalized
     */
    private static List<String> prepareText(String input){
        input = toLowerCase(input);
        input = removeReferences(input);
        input = removeComments(input);
        input = removePunctuation(input);
        input = removeSingleCharacter(input);
        input = input.replaceAll("\\s+"," ");
        input = input.trim();

        return lemmatize(input);
    }

}
